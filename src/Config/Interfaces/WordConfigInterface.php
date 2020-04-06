<?php

namespace App\Config\Interfaces;

interface WordConfigInterface
{
    function wordMinLength() : int;
    function wordMaxLength() : int;
    function wordApprovedAssociationCoeff() : float;
    function wordDislikeCoeff() : float;
    function wordApprovalThreshold() : float;
    function wordMatureThreshold() : float;
    function wordLastAddedLimit() : int;
}
