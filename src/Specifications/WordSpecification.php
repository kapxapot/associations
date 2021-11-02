<?php

namespace App\Specifications;

use App\Config\Interfaces\WordConfigInterface;
use App\Models\Word;
use App\Models\WordRelation;
use App\Semantics\PartOfSpeech;
use App\Semantics\Scope;
use App\Semantics\Severity;

class WordSpecification
{
    private WordConfigInterface $config;

    public function __construct(
        WordConfigInterface $config
    )
    {
        $this->config = $config;
    }

    public function countScope(Word $word): int
    {
        if ($word->hasScopeOverride()) {
            return $word->scopeOverride();
        }

        $relationScopes = [Scope::DISABLED, Scope::INACTIVE];

        foreach ($relationScopes as $scope) {
            if ($this->isScopedByRelations($word, $scope)) {
                return $scope;
            }
        }

        if ($this->isCommon($word)) {
            return Scope::COMMON;
        }

        if ($this->isPublic($word)) {
            return Scope::PUBLIC;
        }

        $maxScope = $this->maxScopeByPartsOfSpeech($word);

        return min(Scope::PRIVATE, $maxScope);
    }

    private function maxScopeByPartsOfSpeech(Word $word): int
    {
        $bestPosQuality = $word->partsOfSpeech()->bestQuality();

        return $this->mapPosQualityToMaxScope($bestPosQuality);
    }

    /**
     * Maps part of speech quality to max scope it can have.
     */
    private function mapPosQualityToMaxScope(?int $quality): int
    {
        switch ($quality) {
            case PartOfSpeech::GOOD:
                return Scope::COMMON;

            case PartOfSpeech::BAD:
                return Scope::PRIVATE;

            case PartOfSpeech::UGLY:
                return Scope::INACTIVE;
        }

        // if there are no parts of speech (quality is null),
        // max scope is private
        return Scope::PRIVATE;
    }

    private function isScopedByRelations(Word $word, int $scope): bool
    {
        $primary = $word->primaryRelation();

        return $primary
            ? $primary->isScopedTo($scope)
            : false;
    }

    private function isPublic(Word $word): bool
    {
        if (!$word->isGoodPartOfSpeech()) {
            return false;
        }

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

        return $word->isGoodPartOfSpeech();
    }

    private function isPublicByDefinition(Word $word): bool
    {
        $definition = $word->definition();

        if ($definition === null || !$definition->isValid()) {
            return false;
        }

        return $word->isGoodPartOfSpeech();
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
        return $this->isPublic($word)
            && !$word->hasMain()
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
