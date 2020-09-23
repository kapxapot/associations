<?php

namespace Brightwood\Models\Messages\Interfaces;

use Brightwood\Models\Data\StoryData;

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

    function data() : ?StoryData;

    /**
     * @return static
     */
    function prependLines(string ...$lines) : self;

    /**
     * @return static
     */
    function appendLines(string ...$lines) : self;
}
