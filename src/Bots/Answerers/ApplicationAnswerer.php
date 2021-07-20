<?php

namespace App\Bots\Answerers;

use App\Bots\AbstractBotRequest;
use App\Bots\BotResponse;
use App\Models\DTO\MetaTurn;
use App\Models\Word;
use App\Repositories\Interfaces\WordRepositoryInterface;
use App\Services\LanguageService;
use Plasticode\Semantics\Sentence;

class ApplicationAnswerer extends AbstractAnswerer
{
    protected const VAR_PREV_WORD = 'prev_word_id';

    private WordRepositoryInterface $wordRepository;

    public function __construct(
        WordRepositoryInterface $wordRepository,
        LanguageService $languageService
    )
    {
        parent::__construct($languageService);

        $this->wordRepository = $wordRepository;
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
            return $this->helpDialog($request);
        }

        if (strlen($question) === 0) {
            return $this->cluelessResponse();
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

    private function helpDialog(AbstractBotRequest $request): BotResponse
    {
        if ($this->isHelpRulesCommand($request)) {
            return $this
                ->buildResponse(
                    self::MESSAGE_RULES_APPLICATION,
                    self::CHUNK_COMMANDS,
                    self::CHUNK_PLAY
                )
                ->withVarBy($request, self::VAR_STATE, self::STATE_RULES);
        }

        if ($this->isHelpCommandsCommand($request)) {
            return $this
                ->buildResponse(
                    self::MESSAGE_COMMANDS_APPLICATION,
                    self::CHUNK_RULES,
                    self::CHUNK_PLAY
                )
                ->withVarBy($request, self::VAR_STATE, self::STATE_COMMANDS);
        }

        if ($this->isPlayCommand($request)) {
            return $this->answerWithAnyWord(
                $request,
                'Я начинаю:'
            );
        }

        return $this->helpCommand(
            $request,
            Sentence::tailPeriod(self::MESSAGE_CLUELESS)
        );
    }

    private function turnToAnswer(AbstractBotRequest $request, MetaTurn $turn): BotResponse
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

        $response = $this->buildResponse($answerParts);

        if ($word !== null) {
            $response->withApplicationVar(self::VAR_PREV_WORD, $word->getId());
        }

        return $response;
    }

    private function getWordFor(?string $question, ?Word $prevWord): MetaTurn
    {
        $word = $this->findWord($question);

        $answerAssociation = $word
            ? $word->randomPublicAssociation($prevWord)
            : null;

        $answer = $answerAssociation
            ? $answerAssociation->otherWord($word)
            : null;

        return new MetaTurn($answerAssociation, $answer, $word);
    }

    private function getAnyWord(?AbstractBotRequest $request = null): ?Word
    {
        $language = $this->getLanguage();

        $word = ($request !== null)
            ? $this->findWord($request->command())
            : null;

        return $this->languageService->getRandomPublicWord($language, $word);
    }
}
