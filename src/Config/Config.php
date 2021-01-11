<?php

namespace App\Config;

use App\Config\Interfaces\AssociationConfigInterface;
use App\Config\Interfaces\NewsConfigInterface;
use App\Config\Interfaces\UserConfigInterface;
use App\Config\Interfaces\WordConfigInterface;
use Plasticode\Config\Config as BaseConfig;

class Config extends BaseConfig implements AssociationConfigInterface, NewsConfigInterface, UserConfigInterface, WordConfigInterface
{
    public function associationUsageCoeff() : float
    {
        return $this->get('associations.coeffs.usage', 1);
    }

    public function associationDislikeCoeff() : float
    {
        return $this->get('associations.coeffs.dislike', 1);
    }

    public function associationApprovalThreshold() : float
    {
        return $this->get('associations.approval_threshold', 2);
    }

    public function associationMatureThreshold() : float
    {
        return $this->get('associations.mature_threshold', 2);
    }

    public function associationLastAddedLimit() : int
    {
        return $this->get('associations.last_added_limit', 10);
    }

    public function newsLatestLimit() : int
    {
        return $this->get('news.latest_limit', 5);
    }

    public function userMatureAge() : int
    {
        return $this->get('users.mature_age', 16);
    }

    public function wordMinLength() : int
    {
        return $this->get('view_globals.word_min_length', 1);
    }
    
    public function wordMaxLength() : int
    {
        return $this->get('view_globals.word_max_length', 250);
    }

    public function wordApprovedAssociationCoeff() : float
    {
        return $this->get('words.coeffs.approved_association', 1);
    }

    public function wordDislikeCoeff() : float
    {
        return $this->get('words.coeffs.dislike', 1);
    }

    public function wordApprovalThreshold() : float
    {
        return $this->get('words.approval_threshold', 1);
    }

    public function wordMatureThreshold() : float
    {
        return $this->get('words.mature_threshold', 2);
    }

    public function wordLastAddedLimit() : int
    {
        return $this->get('words.last_added_limit', 10);
    }
}
