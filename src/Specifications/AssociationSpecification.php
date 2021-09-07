<?php

namespace App\Specifications;

use App\Config\Interfaces\AssociationConfigInterface;
use App\Models\Association;
use App\Models\Word;
use App\Semantics\Scope;
use App\Semantics\Severity;
use App\Specifications\Rules\AbstractRule;
use App\Specifications\Rules\Association\AssociationDisabledByOverride;
use App\Specifications\Rules\Association\AssociationDisabledByWords;
use App\Specifications\Rules\Association\AssociationDisabledByWordsRelation;
use Plasticode\Collections\Generic\Collection;

class AssociationSpecification
{
    private AssociationConfigInterface $config;
    private WordSpecification $wordSpecification;

    public function __construct(
        AssociationConfigInterface $config,
        WordSpecification $wordSpecification
    )
    {
        $this->config = $config;
        $this->wordSpecification = $wordSpecification;
    }

    public function countScope(Association $association): int
    {
        if ($association->hasScopeOverride()) {
            return $association->scopeOverride();
        }

        if ($this->isDisabled($association)) {
            return Scope::DISABLED;
        }

        if ($this->isApproved($association)) {
            return Scope::PUBLIC;
        }

        return min([Scope::PRIVATE, $association->minWordScope()]);
    }

    private function isDisabled(Association $association): bool
    {
        $rules = Collection::collect(
            new AssociationDisabledByWords(),
            new AssociationDisabledByWordsRelation()
        );

        return $rules->anyFirst(
            fn (AbstractRule $r) => $r->check($association)
        );
    }

    private function isApproved(Association $association): bool
    {
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
