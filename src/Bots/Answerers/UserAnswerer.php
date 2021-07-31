<?php

namespace App\Bots\Answerers;

use App\Bots\AbstractBotRequest;
use App\Bots\BotResponse;
use App\Bots\Command;
use App\Exceptions\TurnException;
use App\Models\AbstractBotUser;
use App\Models\Game;
use App\Models\Turn;
use App\Models\Word;
use App\Semantics\Tokenizer;
use App\Services\AssociationFeedbackService;
use App\Services\GameService;
use App\Services\LanguageService;
use App\Services\TurnService;
use App\Services\WordFeedbackService;
use InvalidArgumentException;
use Plasticode\Core\Interfaces\TranslatorInterface;
use Plasticode\Exceptions\ValidationException;
use Plasticode\Semantics\Sentence;
use Plasticode\Traits\LoggerAwareTrait;
use Plasticode\Util\Strings;
use Psr\Log\LoggerInterface;
use Webmozart\Assert\Assert;

class UserAnswerer extends AbstractAnswerer
{
    use LoggerAwareTrait;

    private const MAX_TOKENS = 3;
    public const WORD_LIMIT = 'трёх слов';

    private AssociationFeedbackService $associationFeedbackService;
    private GameService $gameService;
    private TurnService $turnService;
    private WordFeedbackService $wordFeedbackService;

    private TranslatorInterface $translator;

    private Tokenizer $tokenizer;

    public function __construct(
        AssociationFeedbackService $associationFeedbackService,
        GameService $gameService,
        LanguageService $languageService,
        TurnService $turnService,
        WordFeedbackService $wordFeedbackService,
        TranslatorInterface $translator,
        LoggerInterface $logger
    )
    {
        parent::__construct($languageService);

        $this->associationFeedbackService = $associationFeedbackService;
        $this->gameService = $gameService;
        $this->turnService = $turnService;
        $this->wordFeedbackService = $wordFeedbackService;

        $this->translator = $translator;

        $this->logger = $logger;

        $this->tokenizer = new Tokenizer();
    }

    public function getResponse(
        AbstractBotRequest $request,
        AbstractBotUser $botUser
    ): BotResponse
    {
        Assert::true($botUser->isValid());

        $command = $request->command();
        $tokens = $request->tokens();
        $isNewSession = $request->isNewSession();

        if ($isNewSession) {
            return $this->startCommand($botUser, $request);
        }

        if ($this->isHelpDialog($request)) {
            return $this->helpDialog(
                $request,
                fn () => $this->currentGameResponse(
                    $botUser,
                    $botUser->isNew() ? 'Я начинаю:' : 'Я продолжаю:'
                )
            );
        }

        if ($this->isConfirmDialog($request)) {
            return $this->checkCommandConfirmation($botUser, $request);
        }

        if (strlen($command) == 0) {
            return $this->cluelessResponse();
        }

        if ($request->isAny(
            Command::HELP,
            Command::COMMANDS,
            Command::RULES
        )) {
            // no need to confirm a command when it's triggered by a button click
            if ($request->isButtonPressed()) {
                return $this->applyHelpSubCommand($request, $command);
            }

            return $this->confirmCommand($command);
        }

        if ($this->isHelpCommand($request)) {
            return $this->helpCommand($request);
        }

        if ($this->isHelpRulesCommand($request)) {
            return $this->rulesCommand($request);
        }

        if ($this->isNativeBotCommand($request)) {
            return $this->nativeBotCommand($botUser);
        }

        if ($this->isWhatCommand($request)) {
            return $this->whatCommand($botUser, $request);
        }

        if ($this->isRepeatCommand($request)) {
            return $this->repeatCommand($botUser);
        }

        if ($request->isAny(
            Command::WORD_DISLIKE,
            'не нравится',
            'не нравится слово'
        )) {
            return $this->wordDislikeFeedback($botUser);
        }

        if ($request->isAny(
            Command::ASSOCIATION_DISLIKE,
            'плохой ассоциация',
            'не нравится ассоциация'
        )) {
            return $this->associationDislikeFeedback($botUser);
        }

        if ($this->isSkipCommand($request)) {
            return $this->skipCommand($botUser);
        }

        if (count($tokens) > self::MAX_TOKENS) {
            return $this->tooManyWords($botUser);
        }

        return $this->sayWord($botUser, $command);
    }

    private function startCommand(
        AbstractBotUser $botUser,
        AbstractBotRequest $request): BotResponse
    {
        if ($botUser->isNew()) {
            return $this->helpCommand($request, self::MESSAGE_WELCOME);
        }

        return $this->currentGameResponse(
            $botUser,
            self::MESSAGE_WELCOME_BACK,
            'Я продолжаю:'
        );
    }

    private function confirmCommand(
        string $command,
        string ...$prependMessages
    ): BotResponse
    {
        return $this
            ->buildResponse(
                $prependMessages,
                'Для подтверждения команды {cmd:' . $command . '} скажи{att:те} {cmd:command} или повтори{att:те} её. Если {att:в|т}ы хо{att:тите|чешь} сказать это слово в игре, скажи{att:те} {cmd:playing}.'
            )
            ->withUserVar(self::VAR_STATE, self::STATE_COMMAND_CONFIRM)
            ->withUserVar(self::VAR_COMMAND, $command)
            ->withActions(
                $command,
                Command::PLAYING
            );
    }

    private function isConfirmDialog(AbstractBotRequest $request): bool
    {
        return $request->var(self::VAR_STATE) === self::STATE_COMMAND_CONFIRM
            && $request->var(self::VAR_COMMAND) !== null;
    }

    private function checkCommandConfirmation(
        AbstractBotUser $botUser,
        AbstractBotRequest $request
    ): BotResponse
    {
        $command = $request->command();
        $commandToConfirm = $request->var(self::VAR_COMMAND);

        if ($command === Command::COMMAND || $command === $commandToConfirm) {
            return $this->applyHelpSubCommand($request, $commandToConfirm);
        }

        if ($this->isPlayCommand($request)) {
            return $this->sayWord($botUser, $commandToConfirm);
        }

        return $this->confirmCommand($commandToConfirm, self::MESSAGE_CLUELESS);
    }

    private function applyHelpSubCommand(
        AbstractBotRequest $request,
        string $command
    ): BotResponse
    {
        switch ($command) {
            case Command::HELP:
                return $this->helpCommand($request);

            case Command::RULES:
                return $this->rulesCommand($request);

            case Command::COMMANDS:
                return $this->commandsCommand($request);
        }

        throw new InvalidArgumentException('Unknown help sub-command: ' . $command);
    }

    protected function getCommandsMessage(): string
    {
        return self::MESSAGE_COMMANDS_USER;
    }

    private function nativeBotCommand(AbstractBotUser $botUser): BotResponse
    {
        return $this->currentGameResponse(
            $botUser,
            'Я не могу выполнить эту команду в игре. Скажи{att:те} {cmd:enough}, чтобы выйти.',
            'А мое слово:'
        );
    }

    private function whatCommand(
        AbstractBotUser $botUser,
        AbstractBotRequest $request
    ): BotResponse
    {
        $matches = $request->matches('что такое *')
            ?? $request->matches('* это что')
            ?? $request->matches('* что это')
            ?? $request->matches('* это что такое')
            ?? $request->matches('* что это такое');

        $askedFor = !empty($matches)
            ? $matches[0]
            : null;

        $lastWord = $this->getLastTurn($botUser)->word();

        $word = ($askedFor !== null)
            ? $this->findWord($askedFor)
            : $lastWord;

        $definition = $this->getDefinition($word);

        if ($lastWord->equals($word)) {
            return $this
                ->buildResponse($definition)
                ->withActions(
                    ...$this->getCommands($this->getGame($botUser))
                );
        }

        return $this->currentGameResponse(
            $botUser,
            Sentence::tailPeriod($definition),
            'Итак, я говорю:'
        );
    }

    private function getDefinition(?Word $word): string
    {
        if ($word === null) {
            return 'Я не знаю такого слова.';
        }

        $wordStr = $word->word;
        $parsedDefinition = $word->parsedDefinition();

        if ($parsedDefinition === null) {
            return 'Я не знаю, что такое {q:' . $wordStr . '}.';
        }

        return Strings::upperCaseFirst($wordStr) . ' — это '
            . Strings::lowerCaseFirst($parsedDefinition->firstDefinition());
    }

    private function tooManyWords(AbstractBotUser $botUser): BotResponse
    {
        return $this->currentGameResponse(
            $botUser,
            'Давай{att:те} не больше {word_limit} сразу. Итак, я говорю:'
        );
    }

    private function wordDislikeFeedback(AbstractBotUser $botUser): BotResponse
    {
        $word = $this->getLastTurn($botUser)->word();

        $this->wordFeedbackService->save(
            ['word_id' => $word->getId(), 'dislike' => true],
            $botUser->user()
        );

        $this->finishGameFor($botUser);

        return $this->newGameResponse(
            $botUser,
            'Спасибо, {att:ваш|твой} отзыв сохранен.',
            self::MESSAGE_START_ANEW
        );
    }

    private function associationDislikeFeedback(AbstractBotUser $botUser): BotResponse
    {
        $association = $this->getLastTurn($botUser)->association();

        if ($association === null) {
            return $this->currentGameResponse(
                $botUser,
                'Названо только одно слово, и ассоциации ещё нет. Скажи{att:те} {cmd:word_dislike}, если {att:вам|тебе} не нравится слово.',
                'Я говорю:'
            );
        }

        $this->associationFeedbackService->save(
            ['association_id' => $association->getId(), 'dislike' => true],
            $botUser->user()
        );

        $this->finishGameFor($botUser);

        return $this->newGameResponse(
            $botUser,
            'Спасибо, {att:ваш|твой} отзыв сохранен.',
            self::MESSAGE_START_ANEW
        );
    }

    private function skipCommand(AbstractBotUser $botUser): BotResponse
    {
        $this->finishGameFor($botUser);

        return $this->newGameResponse(
            $botUser,
            self::MESSAGE_SKIP,
            self::MESSAGE_START_ANEW
        );
    }

    private function repeatCommand(AbstractBotUser $botUser): BotResponse
    {
        return $this->currentGameResponse(
            $botUser,
            $this->randomString('Повторяю', 'Хорошо', 'Ещё раз', 'Моё слово', 'Я говорю') . ':'
        );
    }

    private function sayWord(AbstractBotUser $botUser, string $question): BotResponse
    {
        $user = $botUser->user();
        $game = $this->getGame($botUser);

        $prevWord = $game->lastTurnWord();

        if ($prevWord !== null) {
            $question = $this->purgeWord($question, $prevWord->word);
        }

        $question = $this->deduplicate($question);

        try {
            $turns = $this->gameService->makeTurn($user, $game, $question);
        } catch (ValidationException $vEx) {
            return $this
                ->buildResponse(
                    $vEx->firstError()
                )
                ->withActions(
                    ...$this->getCommands($game)
                );
        } catch (TurnException $tEx) {
            return $this
                ->buildResponse(
                    $tEx->getTranslatedMessage($this->translator)
                )
                ->withActions(
                    ...$this->getCommands($game)
                );
        }

        $answerParts = [];

        $questionWord = $this->findWord($question);

        $isMatureQuestion = $questionWord !== null && $questionWord->isMature();

        if ($isMatureQuestion) {
            $answerParts[] = $this->matureWordMessage();
        }

        if ($turns->count() > 1) {
            // continuing current game
            $answerParts[] = $this->renderAiTurn($turns->skip(1)->first());

            return $this
                ->buildResponse($answerParts)
                ->withActions(
                    ...$this->getCommands($game)
                );
        }

        if (!$isMatureQuestion) {
            $answerParts[] = $this->noAssociationMessage();
        }

        $answerParts[] = self::MESSAGE_START_ANEW;

        // no answer, starting new game
        return $this->newGameResponse(
            $botUser,
            ...$answerParts
        );
    }

    /**
     * Removes the previous word from the question if it is contained there.
     */
    private function purgeWord(string $question, string $prevWord): string
    {
        $tokens = $this->tokenizer->tokenize($question);
        $prevWordTokens = $this->tokenizer->tokenize($prevWord);

        $filteredTokens = array_filter(
            $tokens,
            fn (string $token) => !in_array($token, $prevWordTokens)
        );

        if (empty($filteredTokens)) {
            return $question;
        }

        return $this->tokenizer->join($filteredTokens);
    }

    /**
     * Converts 'word word' to 'word' for known words.
     */
    private function deduplicate(string $question): string
    {
        $tokens = $this->tokenizer->tokenize($question);

        $originalCount = count($tokens);

        if ($originalCount <= 1) {
            return $question;
        }

        $deduplicatedTokens = array_unique($tokens);

        if (count($deduplicatedTokens) !== 1) {
            return $question;
        }

        $originalWord = $this->findWord($question);

        if ($originalWord !== null) {
            return $question;
        }

        $deduplicatedCandidate = $deduplicatedTokens[0];

        $deduplicatedWord = $this->findWord($deduplicatedCandidate);

        return $deduplicatedWord !== null
            ? $deduplicatedWord->word
            : $question;
    }

    private function finishGameFor(AbstractBotUser $botUser): void
    {
        $game = $this->getGame($botUser);

        $this->turnService->finishGame($game);
    }

    private function getLastTurn(AbstractBotUser $botUser): Turn
    {
        $turn = $this->getGame($botUser)->lastTurn();

        Assert::notNull($turn);

        return $turn;
    }

    /**
     * Gets the current game or creates a new one for the user.
     */
    private function getGame(AbstractBotUser $botUser): Game
    {
        $user = $botUser->user();

        $game = $this->gameService->getOrCreateGameFor($user);

        Assert::notNull($game);

        return $game;
    }

    private function renderLastTurn(Game $game): string
    {
        return $this->renderAiTurn($game->lastTurn());
    }

    private function renderAiTurn(?Turn $turn): string
    {
        Assert::true($turn === null || $turn->isAiTurn());

        return $turn !== null
            ? $this->renderWord($turn->word())
            : 'Мне нечего сказать. Начинай{att:те} {att:в|т}ы.';
    }

    /**
     * @param array<string[]|string> $parts
     */
    private function newGameResponse(AbstractBotUser $botUser, ...$parts): BotResponse
    {
        $user = $botUser->user();

        $newGame = $this->gameService->createGameFor($user);

        return $this->buildGameResponse($newGame, ...$parts);
    }

    /**
     * @param array<string[]|string> $parts
     */
    private function currentGameResponse(AbstractBotUser $botUser, ...$parts): BotResponse
    {
        $game = $this->getGame($botUser);

        return $this->buildGameResponse($game, ...$parts);
    }

    /**
     * @param array<string[]|string> $parts
     */
    private function buildGameResponse(Game $game, ...$parts): BotResponse
    {
        return $this
            ->buildResponse(...$parts)
            ->addLines(
                $this->renderLastTurn($game)
            )
            ->withActions(
                ...$this->getCommands($game)
            );
    }

    /**
     * @return string[]
     */
    private function getCommands(Game $game): array
    {
        $lastTurn = $game->lastTurn();

        $commands = [];

        if ($lastTurn !== null) {
            $word = $lastTurn->word();
            $association = $lastTurn->association();

            if ($word->parsedDefinition() !== null) {
                $commands[] = Command::WHAT;
            }

            $commands[] = Command::SKIP;
            $commands[] = Command::WORD_DISLIKE;

            if ($association !== null) {
                $commands[] = Command::ASSOCIATION_DISLIKE;
            }
        }

        $commands[] = Command::HELP;

        return $commands;
    }

    protected function buildResponse(...$parts): BotResponse
    {
        $response = parent::buildResponse(...$parts);

        $vars = $response->userState();

        foreach ($this->getKnownVars() as $knownVar) {
            if ($vars === null || !array_key_exists($knownVar, $vars)) {
                $response->withUserVar($knownVar, null);
            }
        }

        return $response;
    }
}
