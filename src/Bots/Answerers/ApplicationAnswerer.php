<?php

namespace App\Bots\Answerers;

use App\Bots\AbstractBotRequest;
use App\Bots\BotResponse;
use App\Bots\Command;
use App\Models\DTO\PseudoTurn;
use App\Models\Word;
use App\Repositories\Interfaces\WordRepositoryInterface;
use App\Services\AssociationService;
use App\Services\GameService;
use App\Services\LanguageService;
use App\Services\TurnService;

class ApplicationAnswerer extends AbstractAnswerer
{
    protected const VAR_PREV_WORD = 'prev_word_id';

    private WordRepositoryInterface $wordRepository;

    private AssociationService $associationService;
    private GameService $gameService;
    private TurnService $turnService;

    public function __construct(
        WordRepositoryInterface $wordRepository,
        AssociationService $associationService,
        GameService $gameService,
        TurnService $turnService,
        LanguageService $languageService
    )
    {
        parent::__construct($languageService);

        $this->wordRepository = $wordRepository;

        $this->associationService = $associationService;
        $this->gameService = $gameService;
        $this->turnService = $turnService;
    }

    public function getResponse(AbstractBotRequest $request): BotResponse
    {
        $question = $request->command();
        $isNewSession = $request->isNewSession();

        if ($isNewSession) {
            return $this->helpCommand(
                $request,
                self::MESSAGE_WELCOME,
                self::MESSAGE_DEMO
            );
        }

        if ($this->isHelpDialog($request)) {
            return $this->helpDialog(
                $request,
                fn () => $this->answerWithAnyWord($request, 'Я начинаю:')
            );
        }

        if (strlen($question) == 0) {
            return $this
                ->buildResponse(self::MESSAGE_CLUELESS)
                ->withActions(
                    Command::SKIP,
                    Command::HELP
                );
        }

        $prevWordId = $request->var(self::VAR_PREV_WORD);
        $prevWord = $this->wordRepository->get($prevWordId);

        if ($this->isHelpCommand($request)) {
            return $this->helpCommand($request);
        }

        if ($this->isSkipCommand($request)) {
            return $this->answerWithAnyWord(
                $request,
                self::MESSAGE_SKIP,
                self::MESSAGE_START_ANEW
            );
        }

        $turn = $this->getWordFor($question, $prevWord);

        return $this->turnToAnswer($request, $turn);
    }

    protected function getCommandsMessage(): string
    {
        return self::MESSAGE_COMMANDS_APPLICATION;
    }

    private function turnToAnswer(AbstractBotRequest $request, PseudoTurn $turn): BotResponse
    {
        $questionWord = $turn->prevWord();
        $answerWord = $turn->word();

        $answerParts = [];

        $isMatureQuestion = $questionWord !== null && $questionWord->isMature();

        if ($isMatureQuestion) {
            $answerParts[] = $this->matureWordMessage();
        }

        if ($answerWord !== null) {
            return $this->answerWithWord($answerWord, ...$answerParts);
        }

        if (!$isMatureQuestion) {
            $answerParts[] = $this->noAssociationMessage();
        }

        $answerParts[] = self::MESSAGE_START_ANEW;

        return $this->answerWithAnyWord($request, ...$answerParts);
    }

    private function answerWithAnyWord(
        AbstractBotRequest $request,
        string ...$answerParts
    ): BotResponse
    {
        $word = $this->getAnyWord($request);

        return $this->answerWithWord($word, ...$answerParts);
    }

    private function answerWithWord(
        ?Word $word,
        string ...$answerParts
    ): BotResponse
    {
        $answerParts[] = $this->renderWord($word);

        $response = $this
            ->buildResponse($answerParts)
            ->withActions(
                Command::SKIP,
                Command::HELP
            );

        if ($word !== null) {
            $response->withApplicationVar(self::VAR_PREV_WORD, $word->getId());
        }

        return $response;
    }

    private function getWordFor(?string $question, ?Word $prevWord): PseudoTurn
    {
        $word = $this->findWord($question);

        $game = $this->gameService->buildEtherealGame($prevWord, $word);

        $answer = $word !== null
            ? $this->turnService->findAnswer($game, $word)
            : null;

        $answerAssociation = $answer !== null
            ? $this->associationService->getByPair($word, $answer)
            : null;

        return new PseudoTurn($answerAssociation, $answer, $word);
    }

    private function getAnyWord(?AbstractBotRequest $request = null): ?Word
    {
        $language = $this->getLanguage();

        $word = ($request !== null)
            ? $this->findWord($request->command())
            : null;

        return $this->languageService->getRandomStartingWord($language, $word);
    }
}
