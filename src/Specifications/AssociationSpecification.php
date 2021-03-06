<?php

namespace App\Specifications;

use App\Config\Interfaces\AssociationConfigInterface;
use App\Models\Association;
use App\Models\Word;
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

    public function isDisabled(Association $association): bool
    {
        $rules = Collection::collect(
            new AssociationDisabledByOverride(),
            new AssociationDisabledByWords(),
            new AssociationDisabledByWordsRelation()
        );

        return $rules->anyFirst(
            fn (AbstractRule $r) => $r->check($association)
        );
    }

    public function isApproved(Association $association): bool
    {
        // shortcut
        if ($this->isDisabled($association)) {
            return false;
        }

        if ($association->hasApprovedOverride()) {
            return $association->approvedOverride();
        }

        // any word has approved override and is not approved?
        $anyWordDisapprovedByOverride = $association
            ->words()
            ->map(
                fn (Word $w) => $this->wordSpecification->approvedOverride($w)
            )
            ->any(
                fn (?bool $ao) => $ao === false
            );

        if ($anyWordDisapprovedByOverride) {
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

    public function isMature(Association $association): bool
    {
        if ($association->hasMatureOverride()) {
            return $association->matureOverride();
        }

        return $this->isMatureByWords($association)
            || $this->isMatureByFeedbacks($association);
    }

    private function isMatureByWords(Association $association): bool
    {
        return $association->hasMatureWords();
    }

    private function isMatureByFeedbacks(Association $association): bool
    {
        $threshold = $this->config->associationMatureThreshold();

        $score = $association->matures()->count();

        return $score >= $threshold;
    }
}
