<?php

namespace Brightwood\Tests\Models;

use Brightwood\Models\StoryStatus;
use PHPUnit\Framework\TestCase;

final class StoryStatusTest extends TestCase
{
    public function testPluralAlias(): void
    {
        $this->assertEquals('story_statuses', StoryStatus::pluralAlias());
    }
}
