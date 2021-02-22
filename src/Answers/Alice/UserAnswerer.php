<?php

namespace App\Answers\Alice;

use App\Exceptions\DuplicateWordException;
use App\Models\AliceUser;
use App\Models\DTO\AliceRequest;
use App\Models\DTO\AliceResponse;
use App\Models\Game;
use App\Models\Turn;
use App\Repositories\Interfaces\WordRepositoryInterface;
use App\Services\GameService;
use App\Services\LanguageService;
use App\Services\TurnService;
use Plasticode\Collections\Generic\StringCollection;
use Plasticode\Exceptions\ValidationException;
use Plasticode\Traits\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Webmozart\Assert\Assert;

class UserAnswerer extends AbstractAnswerer
{
    use LoggerAwareTrait;

    private GameService $gameService;
    private TurnService $turnService;

    public function __construct(
        WordRepositoryInterface $wordRepository,
        GameService $gameService,
        LanguageService $languageService,
        TurnService $turnService,
        LoggerInterface $logger
    )
    {
        parent::__construct($wordRepository, $languageService);

        $this->gameService = $gameService;
        $this->turnService = $turnService;

        $this->logger = $logger;
    }

    public function getResponse(AliceRequest $request, AliceUser $aliceUser): AliceResponse
    {
        Assert::true($aliceUser->isValid());

        $question = $request->command;
        $tokens = $request->tokens;
        $isNewSession = $request->isNewSession;

        if ($isNewSession) {
            return $this->startCommand($aliceUser);
        }

        if (strlen($question) === 0) {
            return $this->emptyQuestionResponse();
        }

        // if ($this->whatCommand($tokens)) {

        // }

        if (in_array($question, ['повтори', 'еще раз', 'я не расслышал', 'я не расслышала'])) {
            return $this->repeatCommand($aliceUser);
        }

        if ($this->isHelpCommand($question)) {
            return $this->helpCommand($aliceUser);
        }

        if ($this->isSkipCommand($question)) {
            return $this->skipCommand($aliceUser);
        }

        if (count($request->tokens) > 2) {
            return $this->tooManyWords($aliceUser);
        }

        return $this->sayWord($aliceUser, $question);
    }

    private function startCommand(AliceUser $aliceUser): AliceResponse
    {
        $greeting = $aliceUser->isNew()
            ? self::MESSAGE_WELCOME
            : self::MESSAGE_WELCOME_BACK;

        return $this->buildResponse(
            $greeting,
            $this->renderGameFor($aliceUser)
        );
    }

    private function tooManyWords(AliceUser $aliceUser): AliceResponse
    {
        return $this->buildResponse(
            'Давайте не больше двух слов сразу. Итак, я говорю:',
            $this->renderGameFor($aliceUser)
        );
    }

    private function helpCommand(AliceUser $aliceUser): AliceResponse
    {
        return $this->buildResponse(
            self::MESSAGE_HELP,
            $this->renderGameFor($aliceUser)
        );
    }

    private function skipCommand(AliceUser $aliceUser): AliceResponse
    {
        $game = $this->getGame($aliceUser);

        $this->turnService->finishGame($game);

        return $this->buildResponse(
            self::MESSAGE_SKIP,
            self::MESSAGE_START_ANEW,
            $this->newGameFor($aliceUser)
        );
    }

    private function repeatCommand(AliceUser $aliceUser): AliceResponse
    {
        return $this->buildResponse(
            $this->randomString('Повторяю', 'Хорошо', 'Еще раз') . ':',
            $this->renderGameFor($aliceUser)
        );
    }

    private function sayWord(AliceUser $aliceUser, string $question): AliceResponse
    {
        $user = $aliceUser->user();
        $game = $this->getGame($aliceUser);

        try {
            $turns = $this->gameService->makeTurn($user, $game, $question);
        } catch (ValidationException $vEx) {
            return $this->buildResponse(
                $vEx->firstError()
            );
        } catch (DuplicateWordException $dwEx) {
            $word = $this->renderWordStr($dwEx->word);

            return $this->buildResponse(
                'Слово ' . $word . ' уже использовано в игре.'
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

            return $this->buildResponse(...$answerParts);
        }

        if (!$isMatureQuestion) {
            $answerParts[] = $this->noAssociationMessage();
        }

        $answerParts[] = self::MESSAGE_START_ANEW;

        $answerParts[] = $this->newGameFor($aliceUser);

        // no answer, starting new game
        return $this->buildResponse(...$answerParts);
    }

    private function newGameFor(AliceUser $aliceUser): string
    {
        $user = $aliceUser->user();

        $newGame = $this->gameService->createGameFor($user);

        return $this->renderLastTurn($newGame);
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
            : 'Мне нечего сказать. Начинайте вы.';
    }
}
