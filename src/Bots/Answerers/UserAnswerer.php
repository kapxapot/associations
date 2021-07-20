<?php

namespace App\Bots\Answerers;

use App\Bots\AbstractBotRequest;
use App\Bots\BotResponse;
use App\Exceptions\TurnException;
use App\Models\AliceUser;
use App\Models\Game;
use App\Models\Turn;
use App\Models\Word;
use App\Semantics\Tokenizer;
use App\Services\AssociationFeedbackService;
use App\Services\GameService;
use App\Services\LanguageService;
use App\Services\TurnService;
use App\Services\WordFeedbackService;
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
        AliceUser $aliceUser
    ): BotResponse
    {
        Assert::true($aliceUser->isValid());

        $command = $request->command();
        $tokens = $request->tokens();
        $isNewSession = $request->isNewSession();

        if ($isNewSession) {
            return $this->startCommand($aliceUser, $request);
        }

        if ($this->isHelpDialog($request)) {
            return $this->helpDialog($aliceUser, $request);
        }

        if ($this->isConfirmDialog($request)) {
            return $this->checkCommandConfirmation($aliceUser, $request);
        }

        if (strlen($command) === 0) {
            return $this->cluelessResponse();
        }

        if ($request->isAny(
            self::COMMAND_HELP,
            self::COMMAND_COMMANDS,
            self::COMMAND_RULES
        )) {
            return $this->confirmCommand($command);
        }

        if ($this->isHelpCommand($request)) {
            return $this->helpCommand($request);
        }

        if ($this->isHelpRulesCommand($request)) {
            return $this->rulesCommand();
        }

        if ($this->isNativeCommand($request)) {
            return $this->nativeAliceCommand($aliceUser);
        }

        if ($this->isWhatCommand($request)) {
            return $this->whatCommand($aliceUser, $request);
        }

        if ($this->isRepeatCommand($request)) {
            return $this->repeatCommand($aliceUser);
        }

        if ($request->isAny('плохое слово', 'не нравится', 'не нравится слово')) {
            return $this->wordDislikeFeedback($aliceUser);
        }

        if ($request->isAny('плохая ассоциация', 'плохой ассоциация', 'не нравится ассоциация')) {
            return $this->associationDislikeFeedback($aliceUser);
        }

        if ($this->isSkipCommand($request)) {
            return $this->skipCommand($aliceUser);
        }

        if (count($tokens) > self::MAX_TOKENS) {
            return $this->tooManyWords($aliceUser);
        }

        return $this->sayWord($aliceUser, $command);
    }

    private function startCommand(
        AliceUser $aliceUser,
        AbstractBotRequest $request): BotResponse
    {
        if ($aliceUser->isNew()) {
            return $this->helpCommand($request, self::MESSAGE_WELCOME);
        }

        return $this->buildResponse(
            self::MESSAGE_WELCOME_BACK,
            'Я продолжаю:',
            $this->renderGameFor($aliceUser)
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
                'Для подтверждения команды \'' . $command . '\' скажите \'' . self::COMMAND_COMMAND . '\' или повторите ее. Если вы хотите сказать это слово в игре, скажите \'' . self::COMMAND_PLAYING . '\'.'
            )
            ->withUserVar(self::VAR_STATE, self::STATE_COMMAND_CONFIRM)
            ->withUserVar(self::VAR_COMMAND, $command);
    }

    private function isConfirmDialog(AbstractBotRequest $request): bool
    {
        return $request->var(self::VAR_STATE) === self::STATE_COMMAND_CONFIRM
            && $request->var(self::VAR_COMMAND) !== null;
    }

    private function checkCommandConfirmation(
        AliceUser $aliceUser,
        AbstractBotRequest $request
    ): BotResponse
    {
        $command = $request->command();
        $commandToConfirm = $request->var(self::VAR_COMMAND);

        if ($command === self::COMMAND_COMMAND || $command === $commandToConfirm) {
            switch ($commandToConfirm) {
                case self::COMMAND_HELP:
                    return $this->helpCommand($request);

                case self::COMMAND_RULES:
                    return $this->rulesCommand();

                case self::COMMAND_COMMANDS:
                    return $this->commandsCommand();
            }
        }
        
        if ($this->isPlayCommand($request)) {
            return $this->sayWord($aliceUser, $commandToConfirm);
        }

        return $this->confirmCommand(
            $commandToConfirm,
            Sentence::tailPeriod(self::MESSAGE_CLUELESS)
        );
    }

    private function helpDialog(
        AliceUser $aliceUser,
        AbstractBotRequest $request
    ): BotResponse
    {
        if ($this->isHelpRulesCommand($request)) {
            return $this->rulesCommand();
        }

        if ($this->isHelpCommandsCommand($request)) {
            return $this->commandsCommand();
        }

        if ($this->isPlayCommand($request)) {
            return $this
                ->buildResponse(
                    $aliceUser->isNew() ? 'Я начинаю:' : 'Я продолжаю:',
                    $this->renderGameFor($aliceUser)
                );
        }

        return $this->helpCommand(
            $request,
            Sentence::tailPeriod(self::MESSAGE_CLUELESS)
        );
    }

    private function rulesCommand(): BotResponse
    {
        return $this
            ->buildResponse(
                self::MESSAGE_RULES_USER,
                self::CHUNK_COMMANDS,
                self::CHUNK_PLAY
            )
            ->withUserVar(self::VAR_STATE, self::STATE_RULES);
    }

    private function commandsCommand(): BotResponse
    {
        return $this
            ->buildResponse(
                self::MESSAGE_COMMANDS_USER,
                self::CHUNK_RULES,
                self::CHUNK_PLAY
            )
            ->withUserVar(self::VAR_STATE, self::STATE_COMMANDS);
    }

    private function nativeAliceCommand(AliceUser $aliceUser): BotResponse
    {
        return $this->buildResponse(
            'Я не могу выполнить эту команду в игре. Скажите \'хватит\', чтобы выйти. А мое слово:',
            $this->renderGameFor($aliceUser)
        );
    }

    private function whatCommand(
        AliceUser $aliceUser,
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

        $lastWord = $this->getLastTurn($aliceUser)->word();

        $word = ($askedFor !== null)
            ? $this->findWord($askedFor)
            : $lastWord;

        $definition = $this->getDefinition($word);

        if ($lastWord->equals($word)) {
            return $this->buildResponse($definition);
        }

        return $this->buildResponse(
            Sentence::tailPeriod($definition),
            'Итак, я говорю:',
            $this->renderGameFor($aliceUser)
        );
    }

    private function getDefinition(?Word $word): string
    {
        if ($word === null) {
            return 'Я не знаю такого слова';
        }

        $wordStr = $word->word;
        $parsedDefinition = $word->parsedDefinition();

        return ($parsedDefinition !== null)
            ? Strings::upperCaseFirst($wordStr)
                . ' - это '
                . Strings::lowerCaseFirst($parsedDefinition->firstDefinition())
            : 'Я не знаю, что такое "' . $wordStr . '"';
    }

    private function tooManyWords(AliceUser $aliceUser): BotResponse
    {
        return $this->buildResponse(
            'Давайте не больше трех слов сразу. Итак, я говорю:',
            $this->renderGameFor($aliceUser)
        );
    }

    private function wordDislikeFeedback(AliceUser $aliceUser): BotResponse
    {
        $word = $this->getLastTurn($aliceUser)->word();

        $this->wordFeedbackService->save(
            ['word_id' => $word->getId(), 'dislike' => true],
            $aliceUser->user()
        );

        $this->finishGameFor($aliceUser);

        return $this->buildResponse(
            'Спасибо, ваш отзыв сохранен.',
            self::MESSAGE_START_ANEW,
            $this->newGameFor($aliceUser)
        );
    }

    private function associationDislikeFeedback(AliceUser $aliceUser): BotResponse
    {
        $association = $this->getLastTurn($aliceUser)->association();

        if ($association === null) {
            return $this->buildResponse(
                'Я назвала слово без ассоциации, скажите \'плохое слово\' или \'не нравится\', если вам не нравится слово.',
                'Я говорю:',
                $this->renderGameFor($aliceUser)
            );
        }

        $this->associationFeedbackService->save(
            ['association_id' => $association->getId(), 'dislike' => true],
            $aliceUser->user()
        );

        $this->finishGameFor($aliceUser);

        return $this->buildResponse(
            'Спасибо, ваш отзыв сохранен.',
            self::MESSAGE_START_ANEW,
            $this->newGameFor($aliceUser)
        );
    }

    private function skipCommand(AliceUser $aliceUser): BotResponse
    {
        $this->finishGameFor($aliceUser);

        return $this->buildResponse(
            self::MESSAGE_SKIP,
            self::MESSAGE_START_ANEW,
            $this->newGameFor($aliceUser)
        );
    }

    private function repeatCommand(AliceUser $aliceUser): BotResponse
    {
        return $this->buildResponse(
            $this->randomString('Повторяю', 'Хорошо', 'Еще раз', 'Мое слово', 'Я говорю') . ':',
            $this->renderGameFor($aliceUser)
        );
    }

    private function sayWord(AliceUser $aliceUser, string $question): BotResponse
    {
        $user = $aliceUser->user();
        $game = $this->getGame($aliceUser);

        $prevWord = $game->lastTurnWord();

        if ($prevWord !== null) {
            $question = $this->purgeWord($question, $prevWord->word);
        }

        $question = $this->deduplicate($question);

        try {
            $turns = $this->gameService->makeTurn($user, $game, $question);
        } catch (ValidationException $vEx) {
            return $this->buildResponse(
                $vEx->firstError()
            );
        } catch (TurnException $tEx) {
            return $this->buildResponse(
                $tEx->getTranslatedMessage($this->translator)
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

            return $this->buildResponse($answerParts);
        }

        if (!$isMatureQuestion) {
            $answerParts[] = $this->noAssociationMessage();
        }

        $answerParts[] = self::MESSAGE_START_ANEW;

        $answerParts[] = $this->newGameFor($aliceUser);

        // no answer, starting new game
        return $this->buildResponse($answerParts);
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

    private function finishGameFor(AliceUser $aliceUser): void
    {
        $game = $this->getGame($aliceUser);

        $this->turnService->finishGame($game);
    }

    private function newGameFor(AliceUser $aliceUser): string
    {
        $user = $aliceUser->user();

        $newGame = $this->gameService->createGameFor($user);

        return $this->renderLastTurn($newGame);
    }

    private function getLastTurn(AliceUser $aliceUser): Turn
    {
        $turn = $this->getGame($aliceUser)->lastTurn();

        Assert::notNull($turn);

        return $turn;
    }

    /**
     * Gets the current game or creates a new one for the user.
     */
    private function getGame(AliceUser $aliceUser): Game
    {
        $user = $aliceUser->user();

        $game = $this->gameService->getOrCreateGameFor($user);

        Assert::notNull($game);

        return $game;
    }

    private function renderGameFor(AliceUser $aliceUser): string
    {
        $game = $this->getGame($aliceUser);

        return $this->renderLastTurn($game);
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
            : 'Мне нечего сказать. Начинайте вы';
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
