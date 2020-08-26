<?php

namespace Brightwood\Models\Messages\Interfaces;

interface MessageInterface
{
    /**
     * @return string[]
     */
    function lines() : array;

    /**
     * @return string[]
     */
    function actions() : array;

    /**
     * @return static
     */
    function prependLines(string ...$lines) : self;
}
