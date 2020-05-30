<?php

namespace App\Specifications;

use App\Config\Interfaces\AssociationConfigInterface;
use App\Models\Association;

class AssociationSpecification
{
    private AssociationConfigInterface $config;

    public function __construct(
        AssociationConfigInterface $config
    )
    {
        $this->config = $config;
    }

    public function isApproved(Association $assoc) : bool
    {
        $usageCoeff = $this->config->associationUsageCoeff();
        $dislikeCoeff = $this->config->associationDislikeCoeff();
        $threshold = $this->config->associationApprovalThreshold();

        $turnsByUsers = $assoc->turns()->groupByUser();

        $turnCount = count($turnsByUsers);

        $dislikeCount = $assoc->dislikes()->count();

        $score = $turnCount * $usageCoeff - $dislikeCount * $dislikeCoeff;

        return $score >= $threshold;
    }

    public function isMature(Association $assoc) : bool
    {
        return $this->isMatureByWords($assoc)
            || $this->isMatureByFeedbacks($assoc);
    }

    private function isMatureByWords(Association $assoc) : bool
    {
        return $assoc->hasMatureWords();
    }

    private function isMatureByFeedbacks(Association $assoc) : bool
    {
        $threshold = $this->config->associationMatureThreshold();

        $maturesCount = $assoc->matures()->count();

        return $maturesCount >= $threshold;
    }
}
