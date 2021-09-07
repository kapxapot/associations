<?php

namespace App\Testing\Mocks\Config;

use App\Config\Interfaces\WordConfigInterface;

class WordConfigMock implements WordConfigInterface
{
    public function wordMinLength() : int
    {
        return 1;
    }

    public function wordMaxLength() : int
    {
        return 100;
    }

    public function wordApprovedAssociationCoeff() : float
    {
        return 1;
    }

    public function wordDislikeCoeff() : float
    {
        return 1;
    }

    public function wordApprovalThreshold() : float
    {
        return 1;
    }

    public function wordCommonThreshold() : float
    {
        return 10;
    }

    public function wordMatureThreshold() : float
    {
        return 2;
    }

    public function wordLastAddedLimit() : int
    {
        return 10;
    }
}
