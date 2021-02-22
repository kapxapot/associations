<?php

namespace App\Answers\Alice;

use App\Models\DTO\AliceRequest;
use App\Models\DTO\AliceResponse;
use App\Models\DTO\MetaTurn;
use App\Models\Word;

class ApplicationAnswerer extends AbstractAnswerer
{
    protected const VAR_PREV_WORD = 'prev_word_id';

    public function getResponse(AliceRequest $request): AliceResponse
    {
        $question = $request->command;
        $isNewSession = $request->isNewSession;

        if ($isNewSession) {
            return $this->answerWithAnyWord(
                $request,
                self::MESSAGE_WELCOME
            );
        }

        if (strlen($question) === 0) {
            return $this->emptyQuestionResponse();
        }

        $prevWordId = $request->var(self::VAR_PREV_WORD);
        $prevWord = $this->wordRepository->get($prevWordId);

        if ($this->isHelpCommand($question)) {
            return $this
                ->answerWithWord(
                    $request,
                    $prevWord,
                    self::MESSAGE_HELP
                )
                ->withStateFrom($request);
        }

        if ($this->isSkipCommand($question)) {
            return $this->answerWithAnyWord(
                $request,
                self::MESSAGE_SKIP,
                self::MESSAGE_START_ANEW
            );
        }

        $turn = $this->getWordFor($question, $prevWord);

        return $this->turnToAnswer($request, $turn);
    }

    private function turnToAnswer(AliceRequest $request, MetaTurn $turn): AliceResponse
    {
        $questionWord = $turn->prevWord();
        $answerWord = $turn->word();

        $answerParts = [];

        $isMatureQuestion = $questionWord !== null && $questionWord->isMature();

        if ($isMatureQuestion) {
            $answerParts[] = $this->matureWordMessage();
        }

        if ($answerWord !== null) {
            return $this->answerWithWord($request, $answerWord, ...$answerParts);
        }

        if (!$isMatureQuestion) {
            $answerParts[] = $this->noAssociationMessage();
        }

        $answerParts[] = self::MESSAGE_START_ANEW;

        return $this->answerWithAnyWord($request, ...$answerParts);
    }

    private function answerWithAnyWord(
        AliceRequest $request,
        string ...$answerParts
    ): AliceResponse
    {
        $word = $this->getAnyWord($request);

        return $this->answerWithWord($request, $word, ...$answerParts);
    }

    private function answerWithWord(
        AliceRequest $request,
        ?Word $word,
        string ...$answerParts
    ): AliceResponse
    {
        $answerParts[] = $this->renderWord($word);

        $response = $this->buildResponse(...$answerParts);

        if ($word !== null) {
            $response->withVarBy($request, self::VAR_PREV_WORD, $word->getId());
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

    private function getAnyWord(?AliceRequest $request = null): ?Word
    {
        $language = $this->getLanguage();

        $word = ($request !== null)
            ? $this->findWord($request->command)
            : null;

        return $this->languageService->getRandomPublicWord($language, $word);
    }
}
