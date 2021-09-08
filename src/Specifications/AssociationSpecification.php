<?php

namespace App\Specifications;

use App\Config\Interfaces\AssociationConfigInterface;
use App\Models\Association;
use App\Semantics\Scope;
use App\Semantics\Severity;
use App\Specifications\Rules\AbstractRule;
use App\Specifications\Rules\Association\AssociationDisabledByWordsRelation;
use Plasticode\Collections\Generic\Collection;

class AssociationSpecification
{
    private AssociationConfigInterface $config;

    public function __construct(
        AssociationConfigInterface $config
    )
    {
        $this->config = $config;
    }

    public function countScope(Association $association): int
    {
        if ($association->hasScopeOverride()) {
            return $association->scopeOverride();
        }

        if ($this->isDisabled($association)) {
            return Scope::DISABLED;
        }

        $minScope = min([Scope::PRIVATE, $association->minWordScope()]);

        // don't make association public, if any of it's words have scope less than private
        if ($minScope == Scope::PRIVATE && $this->isPublic($association)) {
            return Scope::PUBLIC;
        }

        return $minScope;
    }

    private function isDisabled(Association $association): bool
    {
        $rules = Collection::collect(
            new AssociationDisabledByWordsRelation()
        );

        return $rules->anyFirst(
            fn (AbstractRule $r) => $r->check($association)
        );
    }

    private function isPublic(Association $association): bool
    {
        if (!$association->hasAllGoodPartsOfSpeech()) {
            return false;
        }

        return $this->isApprovedByUsage($association);
    }

    private function isApprovedByUsage(Association $association): bool
    {
        $usageCoeff = $this->config->associationUsageCoeff();
        $dislikeCoeff = $this->config->associationDislikeCoeff();
        $threshold = $this->config->associationApprovalThreshold();

        $turnsByUsers = $association->turns()->groupByUser();

        $turnCount = count($turnsByUsers);

        $dislikeCount = $association->dislikes()->count();

        $score = $turnCount * $usageCoeff - $dislikeCount * $dislikeCoeff;

        return $score >= $threshold;
    }

    public function countSeverity(Association $association): int
    {
        if ($association->hasSeverityOverride()) {
            return $association->severityOverride();
        }

        if ($this->isMature($association)) {
            return Severity::MATURE;
        }

        return $association->maxWordSeverity();
    }

    private function isMature(Association $association): bool
    {
        return $this->isMatureByFeedbacks($association);
    }

    private function isMatureByFeedbacks(Association $association): bool
    {
        $threshold = $this->config->associationMatureThreshold();

        $score = $association->matures()->count();

        return $score >= $threshold;
    }
}
