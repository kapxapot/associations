<?php

namespace Brightwood\Testing\Models;

use Brightwood\Models\Data\StoryData;

/**
 * @property int $day Current day number.
 */
class TestData extends StoryData
{
    protected function init(): void
    {
        $this->day = 1;
    }

    /**
     * Increments the current day.
     *
     * @return $this
     */
    public function nextDay(): self
    {
        $this->day++;

        return $this;
    }
}
