<?php

namespace App\Specifications;

use App\Config\Interfaces\WordConfigInterface;
use App\Models\Word;

class WordSpecification
{
    private WordConfigInterface $config;

    public function __construct(
        WordConfigInterface $config
    )
    {
        $this->config = $config;
    }

    public function isApproved(Word $word) : bool
    {
        return $this->isApprovedByDictWord($word)
            || $this->isApprovedByAssociations($word);
    }

    public function isMature(Word $word) : bool
    {
        $threshold = $this->config->wordMatureThreshold();

        $score = $word->matures()->count();

        return $score >= $threshold;
    }

    private function isApprovedByDictWord(Word $word) : bool
    {
        $dictWord = $word->dictWord();

        return $dictWord && $dictWord->isGood();
    }

    private function isApprovedByAssociations(Word $word) : bool
    {
        $assocCoeff = $this->config->wordApprovedAssociationCoeff();
        $dislikeCoeff = $this->config->wordDislikeCoeff();
        $threshold = $this->config->wordApprovalThreshold();

        $approvedAssocs = $word->approvedAssociations()->count();
        $dislikes = $word->dislikes()->count();

        $score = $approvedAssocs * $assocCoeff - $dislikes * $dislikeCoeff;

        return $score >= $threshold;
    }
}
