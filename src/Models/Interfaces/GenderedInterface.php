<?php

namespace App\Models\Interfaces;

interface GenderedInterface
{
    function hasGender() : bool;

    /**
     * Returns gender if it's defined.
     */
    function gender() : ?int;
}
