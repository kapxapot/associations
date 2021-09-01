<?php

namespace App\Specifications;

use App\Config\Interfaces\WordConfigInterface;
use App\Models\Word;
use App\Models\WordRelation;
use App\Semantics\Scope;
use App\Semantics\Severity;
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

    /**
     * Todo: add override for INACTIVE & COMMON
     */
    public function countScope(Word $word): int
    {
        // if ($word->hasScopeOverride()) {
        //     return $word->scopeOverride();
        // }

        if ($this->isDisabled($word)) {
            return Scope::DISABLED;
        }

        if ($this->isApproved($word)) {
            return Scope::PUBLIC;
        }

        return Scope::PRIVATE;
    }

    private function isDisabled(Word $word): bool
    {
        return $this->isDisabledByOverride($word)
            || $this->isDisabledByRelations($word);
    }

    private function isDisabledByOverride(Word $word): bool
    {
        return $word->hasOverride()
            ? $word->override()->isDisabled()
            : false;
    }

    private function isDisabledByRelations(Word $word): bool
    {
        return $word
            ->relations()
            ->any(
                fn (WordRelation $wr) => $wr->isDisabling()
            );
    }

    private function isApproved(Word $word): bool
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
        // disabled word works as an override as well
        // it has priority over manual override
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

    /**
     * Todo: add override for OFFENDING
     */
    public function countSeverity(Word $word): int
    {
        // if ($word->hasSeverityOverride()) {
        //     return $word->severityOverride();
        // }

        if ($this->isMature($word)) {
            return Severity::MATURE;
        }

        return Severity::NEUTRAL;
    }

    private function isMature(Word $word): bool
    {
        $matureOverride = $word->matureOverride();

        if ($matureOverride !== null) {
            return $matureOverride;
        }

        return $this->isMatureByFeedbacks($word)
            || $this->isMatureByMainWord($word);
    }

    private function isMatureByFeedbacks(Word $word): bool
    {
        $threshold = $this->config->wordMatureThreshold();

        $score = $word->matures()->count();

        return $score >= $threshold;
    }

    private function isMatureByMainWord(Word $word): bool
    {
        return $word->hasMain()
            ? $word->main()->isMature()
            : false;
    }

    public function correctedWord(Word $word): string
    {
        $override = $word->override();

        return $override && $override->hasWordCorrection()
            ? $override->wordCorrection
            : $word->originalWord;
    }
}
