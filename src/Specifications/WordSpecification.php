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

    public function countScope(Word $word): int
    {
        if ($word->hasScopeOverride()) {
            return $word->scopeOverride();
        }

        if ($this->isDisabled($word)) {
            return Scope::DISABLED;
        }

        if ($this->isCommon($word)) {
            return Scope::COMMON;
        }

        if ($this->isPublic($word)) {
            return Scope::PUBLIC;
        }

        return Scope::PRIVATE;
    }

    private function isDisabled(Word $word): bool
    {
        return $this->isDisabledByRelations($word);
    }

    private function isDisabledByRelations(Word $word): bool
    {
        return $word
            ->relations()
            ->any(
                fn (WordRelation $wr) => $wr->isDisabling()
            );
    }

    private function isPublic(Word $word): bool
    {
        return $this->isPublicByDictWord($word)
            || $this->isPublicByDefinition($word)
            || $this->isPublicByAssociations($word);
    }

    private function isPublicByDictWord(Word $word): bool
    {
        $dictWord = $word->dictWord();

        if ($dictWord === null || !$dictWord->isValid()) {
            return false;
        }

        $partsOfSpeech = $word->partsOfSpeechOverride()
            ?? $dictWord->partsOfSpeech();

        return $partsOfSpeech->isAnyGood();
    }

    private function isPublicByDefinition(Word $word): bool
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

    private function isPublicByAssociations(Word $word): bool
    {
        $assocCoeff = $this->config->wordApprovedAssociationCoeff();
        $dislikeCoeff = $this->config->wordDislikeCoeff();
        $threshold = $this->config->wordApprovalThreshold();

        $approvedAssocs = $word->approvedAssociations()->count();
        $dislikes = $word->dislikes()->count();

        $score = $approvedAssocs * $assocCoeff - $dislikes * $dislikeCoeff;

        return $score >= $threshold;
    }

    private function isCommon(Word $word): bool
    {
        return !$word->hasMain()
            && $word->isNeutral()
            && $this->isCommonByAssociations($word);
    }

    private function isCommonByAssociations(Word $word): bool
    {
        $assocCoeff = $this->config->wordApprovedAssociationCoeff();
        $dislikeCoeff = $this->config->wordDislikeCoeff();
        $threshold = $this->config->wordCommonThreshold();

        $approvedAssocs = $word->approvedAssociations()->count();
        $dislikes = $word->dislikes()->count();

        $score = $approvedAssocs * $assocCoeff - $dislikes * $dislikeCoeff;

        return $score >= $threshold;
    }

    public function countSeverity(Word $word): int
    {
        if ($word->hasSeverityOverride()) {
            return $word->severityOverride();
        }

        if ($this->isMature($word)) {
            return Severity::MATURE;
        }

        return $word->hasMain()
            ? $word->main()->severity
            : Severity::NEUTRAL;
    }

    private function isMature(Word $word): bool
    {
        return $this->isMatureByFeedbacks($word);
    }

    private function isMatureByFeedbacks(Word $word): bool
    {
        $threshold = $this->config->wordMatureThreshold();

        $score = $word->matures()->count();

        return $score >= $threshold;
    }

    public function countCorrectedWord(Word $word): string
    {
        $override = $word->override();

        return $override && $override->hasWordCorrection()
            ? $override->wordCorrection
            : $word->originalWord;
    }
}
