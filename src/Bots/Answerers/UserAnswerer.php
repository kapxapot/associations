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
use App\Semantics\Word\WordCleaner;
use App\Services\AssociationFeedbackService;
use App\Services\GameService;
use App\Services\LanguageService;
use App\Services\TurnService;
use App\Services\WordFeedbackService;
use App\Services\WordService;
use InvalidArgumentException;
use Plasticode\Core\Interfaces\TranslatorInterface;
use Plasticode\Exceptions\ValidationException;
use Plasticode\Semantics\Sentence;
use Plasticode\Traits\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Webmozart\Assert\Assert;

class UserAnswerer extends AbstractAnswerer
{
    use LoggerAwareTrait;

    const MAX_TOKENS = 3;
    const WORD_LIMIT = 'трёх слов'; // todo: refactor this

    private AssociationFeedbackService $associationFeedbackService;
    private GameService $gameService;
    private TurnService $turnService;
    private WordService $wordService;
    private WordFeedbackService $wordFeedbackService;

    private TranslatorInterface $translator;
    private WordCleaner $wordCleaner;

    public function __construct(
        AssociationFeedbackService $associationFeedbackService,
        GameService $gameService,
        LanguageService $languageService,
        TurnService $turnService,
        WordService $wordService,
        WordFeedbackService $wordFeedbackService,
        TranslatorInterface $translator,
        LoggerInterface $logger,
        WordCleaner $wordCleaner
    )
    {
        parent::__construct($languageService);

        $this->associationFeedbackService = $associationFeedbackService;
        $this->gameService = $gameService;
        $this->turnService = $turnService;
        $this->wordService = $wordService;
        $this->wordFeedbackService = $wordFeedbackService;

        $this->translator = $translator;
        $this->wordCleaner = $wordCleaner;

        $this->logger = $logger;
    }

    public function getResponse(
        AbstractBotRequest $request,
        AbstractBotUser $botUser
    ): BotResponse
    {
        Assert::true($botUser->isValid());

        $command = $request->command();
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
            return $this->currentGameResponse(
                $botUser,
                self::MESSAGE_CLUELESS,
                'Я говорю:'
            );
        }

        if ($request->isAny(
            Command::HELP,
            Command::RULES,
            Command::COMMANDS,
            Command::EXIT
        )) {
            // no need to confirm a command when it's triggered by a button click
            if ($request->isButtonPressed()) {
                return $this->applyCommand($request, $command);
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

        if ($this->isBadWordCommand($request)) {
            return $this->badWordCommand($botUser);
        }

        if ($this->isBadAssociationCommand($request)) {
            return $this->badAssociationCommand($botUser);
        }

        if ($this->isSkipCommand($request)) {
            return $this->skipCommand($botUser);
        }

        if (count($request->dirtyTokens()) > self::MAX_TOKENS) {
            return $this->tooManyWords($botUser);
        }

        return $this->sayWord($botUser, $command, $request->originalUtterance());
    }

    private function isBadWordCommand(AbstractBotRequest $request)
    {
        return $request->isAny(
            Command::WORD_DISLIKE,
            'не нравится',
            'плохоеслово',
            'плохой слово'
        ) || $request->hasAnySet(
            ['плохое', 'слово'],
            ['не', 'нравится', 'слово']
        );
    }

    private function isBadAssociationCommand(AbstractBotRequest $request)
    {
        return $request->isAny(
            Command::ASSOCIATION_DISLIKE,
            'плохие ассоциации',
            'плохое ассоциация',
            'плохой ассоциация'
        ) || $request->hasAnySet(
            ['не', 'нравится', 'ассоциация'],
            ['неправильная', 'ассоциация'],
            ['плохая', 'ассоциация'],
            ['отмени', 'ассоциацию']
        );
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
                'Для подтверждения команды повтори{{att:те}} её или скажи{{att:те}} слово {{cmd:command}}. Если {{att:в|т}}ы хо{{att:тите|чешь}} сказать слово {{cmd:' . $command . '}} в игре, скажи{{att:те}} {{cmd:playing}}.'
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
            return $this->applyCommand($request, $commandToConfirm);
        }

        if ($this->isPlayCommand($request)) {
            return $this->sayWord($botUser, $commandToConfirm);
        }

        return $this->confirmCommand($commandToConfirm, self::MESSAGE_CLUELESS);
    }

    private function applyCommand(
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

            case Command::EXIT:
                return $this->exitCommand();
        }

        throw new InvalidArgumentException('Unknown command: ' . $command);
    }

    protected function getCommandsMessage(): string
    {
        return self::MESSAGE_COMMANDS_USER;
    }

    private function nativeBotCommand(AbstractBotUser $botUser): BotResponse
    {
        return $this->currentGameResponse(
            $botUser,
            'Я не могу выполнить эту команду в игре. Скажи{{att:те}} {{cmd:enough}}, чтобы выйти.',
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
            Sentence::terminate($definition),
            'Итак, я говорю:'
        );
    }

    private function getDefinition(?Word $word): string
    {
        if ($word === null) {
            return 'Я не знаю такого слова.';
        }

        $wordStr = $word->word;

        $parsedDefinition = $this->wordService->getParsedTransitiveDefinition($word);

        if ($parsedDefinition === null) {
            return 'Я не знаю, что такое {{q:' . $wordStr . '}}.';
        }

        $definitionWord = $parsedDefinition->word();

        return Sentence::buildCased([
            $definitionWord->word,
            ' — это ',
            $parsedDefinition->firstDefinition(),
        ]);
    }

    private function tooManyWords(AbstractBotUser $botUser): BotResponse
    {
        return $this->currentGameResponse(
            $botUser,
            'Давай{{att:те}} не больше {{word_limit}} сразу. Итак, я говорю:'
        );
    }

    private function badWordCommand(AbstractBotUser $botUser): BotResponse
    {
        $word = $this->getLastTurn($botUser)->word();

        $this->wordFeedbackService->dislike($word, $botUser->user());

        $this->finishGameFor($botUser);

        return $this->newGameResponse(
            $botUser,
            'Спасибо, {{att:ваш|твой}} отзыв сохранен.',
            self::MESSAGE_START_ANEW
        );
    }

    private function badAssociationCommand(AbstractBotUser $botUser): BotResponse
    {
        $association = $this->getLastTurn($botUser)->association();

        if ($association === null) {
            return $this->currentGameResponse(
                $botUser,
                'Названо только одно слово, и ассоциации ещё нет. Скажи{{att:те}} {{cmd:word_dislike}}, если {{att:вам|тебе}} не нравится слово.',
                'Я говорю:'
            );
        }

        $this->associationFeedbackService->dislike($association, $botUser->user());

        $this->finishGameFor($botUser);

        return $this->newGameResponse(
            $botUser,
            'Спасибо, {{att:ваш|твой}} отзыв сохранен.',
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

    private function sayWord(
        AbstractBotUser $botUser,
        string $wordStr,
        ?string $originalUtterance = null
    ): BotResponse
    {
        $originalUtterance ??= $wordStr;

        $user = $botUser->user();
        $game = $this->getGame($botUser);

        // 1. try to find a word by original utterance
        // 2. otherwise look for a word by word string
        $word = $this->findWord($originalUtterance);

        if ($word) {
            $wordStr = $originalUtterance;
        } else {
            $wordStr = $this->wordCleaner->clean($wordStr, $game);
            $word = $this->findWord($wordStr);
        }

        try {
            $turns = $this->gameService->makeTurn($user, $game, $wordStr, $originalUtterance);
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

        if ($word && $word->isMature()) {
            $answerParts[] = $this->matureWordMessage();
        }

        if ($turns->count() > 1) {
            // continuing current game
            $aiTurn = $turns->second();
            $answerParts[] = $this->renderAiTurn($aiTurn);

            return $this
                ->buildResponse($answerParts)
                ->withActions(
                    ...$this->getCommands($game)
                );
        }

        // no answer, starting new game
        $answerParts[] = $this->noAssociationMessage();
        $answerParts[] = self::MESSAGE_START_ANEW;

        return $this->newGameResponse(
            $botUser,
            ...$answerParts
        );
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

        $game = $this->gameService->getOrCreateNewGameFor($user);

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
            : 'Мне нечего сказать. Начинай{{att:те}} {{att:в|т}}ы.';
    }

    /**
     * @param array<string[]|string> $parts
     */
    private function newGameResponse(AbstractBotUser $botUser, ...$parts): BotResponse
    {
        $user = $botUser->user();

        $newGame = $this->gameService->createNewGameFor($user);

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
