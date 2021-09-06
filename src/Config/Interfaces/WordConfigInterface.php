<?php

namespace App\Config\Interfaces;

interface WordConfigInterface
{
    public function wordMinLength() : int;

    public function wordMaxLength() : int;

    public function wordApprovedAssociationCoeff() : float;

    public function wordDislikeCoeff() : float;

    public function wordApprovalThreshold() : float;

    public function wordMatureThreshold() : float;

    public function wordLastAddedLimit() : int;
}
