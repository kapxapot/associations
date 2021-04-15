<?php

namespace App\Specifications;

use App\Config\Interfaces\WordConfigInterface;
use App\Models\Word;
use App\Services\WordService;

class WordSpecification
{
    private WordConfigInterface $config;
    private WordService $wordService;

    public function __construct(
        WordConfigInterface $config,
        WordService $wordService
    )
    {
        $this->config = $config;
        $this->wordService = $wordService;
    }

    public function isDisabled(Word $word): bool
    {
        return $word->hasOverride()
            ? $word->override()->isDisabled()
            : false;
    }

    public function isApproved(Word $word): bool
    {
        $approvedOverride = $this->approvedOverride($word);

        if ($approvedOverride !== null) {
            return $approvedOverride;
        }

        return $this->isApprovedByDictWord($word)
            || $this->isApprovedByDefinition($word)
            || $this->isApprovedByAssociations($word);
    }

    public function approvedOverride(Word $word): ?bool
    {
        // shortcut
        if ($this->isDisabled($word)) {
            return false;
        }

        return $word->approvedOverride();
    }

    private function isApprovedByDictWord(Word $word): bool
    {
        $dictWord = $word->dictWord();

        if ($dictWord === null || !$dictWord->isValid()) {
            return false;
        }

        $partsOfSpeech = $word->partsOfSpeechOverride()
            ?? $dictWord->partsOfSpeech();

        return $partsOfSpeech->isAnyGood();
    }

    private function isApprovedByDefinition(Word $word): bool
    {
        $definition = $word->definition();

        if ($definition === null || !$definition->isValid()) {
            return false;
        }

        $parsedDefinition = $this->wordService->getParsedDefinition($word);

        $partsOfSpeech = $word->partsOfSpeechOverride()
            ?? $parsedDefinition->partsOfSpeech();

        return $partsOfSpeech->isAnyGood();
    }

    private function isApprovedByAssociations(Word $word): bool
    {
        $assocCoeff = $this->config->wordApprovedAssociationCoeff();
        $dislikeCoeff = $this->config->wordDislikeCoeff();
        $threshold = $this->config->wordApprovalThreshold();

        $approvedAssocs = $word->approvedAssociations()->count();
        $dislikes = $word->dislikes()->count();

        $score = $approvedAssocs * $assocCoeff - $dislikes * $dislikeCoeff;

        return $score >= $threshold;
    }

    public function isMature(Word $word): bool
    {
        $matureOverride = $word->matureOverride();

        if ($matureOverride !== null) {
            return $matureOverride;
        }

        return $this->isMatureByFeedbacks($word);
    }

    private function isMatureByFeedbacks(Word $word): bool
    {
        $threshold = $this->config->wordMatureThreshold();

        $score = $word->matures()->count();

        return $score >= $threshold;
    }

    public function correctedWord(Word $word): string
    {
        $override = $word->override();

        return $override && $override->hasWordCorrection()
            ? $override->wordCorrection
            : $word->originalWord;
    }
}
