<?php

namespace App\Config\Interfaces;

interface AssociationConfigInterface
{
    function associationUsageCoeff() : float;
    function associationDislikeCoeff() : float;
    function associationApprovalThreshold() : float;
    function associationMatureThreshold() : float;
    function associationLastAddedLimit() : int;
}
