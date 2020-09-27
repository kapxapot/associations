<?php

namespace Brightwood\Models\Messages\Interfaces;

use Brightwood\Models\Data\StoryData;

interface MessageInterface
{
    /**
     * @return string[]
     */
    function lines() : array;

    function prependLines(string ...$lines) : self;
    function appendLines(string ...$lines) : self;

    /**
     * @return string[]
     */
    function actions() : array;

    function hasActions() : bool;
    function appendActions(string ...$actions) : self;

    function data() : ?StoryData;
    function hasData() : bool;
}
