<?php

namespace App\Specifications;

use App\Config\Interfaces\WordConfigInterface;
use App\Models\Word;
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

        $calculatedScope = $this->calculateScope($word);
        $maxScope = $this->maxScope($word);

        // restrict scope only if it's limited by a fuzzy disabled scope
        return Scope::isFuzzyDisabled($maxScope)
            ? min($calculatedScope, $maxScope)
            : $calculatedScope;
    }

    /**
     * Counts scope without applying the max scope.
     */
    private function calculateScope(Word $word): int
    {
        $relationsScopeOverride = $this->relationsScopeOverride($word);

        if ($relationsScopeOverride !== null) {
            return $relationsScopeOverride;
        }

        if ($this->isCommon($word)) {
            return Scope::COMMON;
        }

        if ($this->isPublic($word)) {
            return Scope::PUBLIC;
        }

        return Scope::PRIVATE;
    }

    /**
     * Returns max scope for the word, applying all restrictions.
     */
    private function maxScope(Word $word): int
    {
        return min(
            $this->maxScopeByMainWord($word) ?? Scope::max(),
            $this->maxScopeByPartsOfSpeech($word)
        );
    }

    private function maxScopeByMainWord(Word $word): ?int
    {
        return $word->hasMain()
            ? $this->countScope($word->main())
            : null;
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

    /**
     * Returns any scope override by relations.
     */
    private function relationsScopeOverride(Word $word): ?int
    {
        $primaryRelation = $word->primaryRelation();

        return $primaryRelation
            ? $primaryRelation->scopeOverride()
            : null;
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
