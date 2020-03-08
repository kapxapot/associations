<?php

namespace App\Config\Interfaces;

interface AssociationConfigInterface
{
    public function associationUsageCoeff() : float;
    public function associationDislikeCoeff() : float;
    public function associationApprovalThreshold() : float;
    public function associationMatureThreshold() : float;
}
