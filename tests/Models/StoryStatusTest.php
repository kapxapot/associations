<?php

namespace App\Tests\Models;

use App\Models\Brightwood\StoryStatus;
use PHPUnit\Framework\TestCase;

class StoryStatusTest extends TestCase
{
    public function testPluralAlias() : void
    {
        $this->assertEquals('story_statuses', StoryStatus::pluralAlias());
    }
}
