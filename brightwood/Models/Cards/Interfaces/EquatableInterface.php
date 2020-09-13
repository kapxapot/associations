<?php

namespace Brightwood\Models\Cards\Interfaces;

interface EquatableInterface
{
    /**
     * @param static|null $obj
     */
    function equals(?self $obj) : bool;
}
