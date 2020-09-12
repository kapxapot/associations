<?php

namespace Brightwood\Models\Interfaces;

interface EquatableInterface
{
    /**
     * @param static|null $obj
     */
    function equals(?self $obj) : bool;
}
