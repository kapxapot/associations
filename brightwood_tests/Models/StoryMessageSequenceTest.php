<?php

namespace Brightwood\Tests\Models;

use Brightwood\Models\Messages\StoryMessageSequence;
use Brightwood\Models\Messages\TextMessage;
use PHPUnit\Framework\TestCase;

final class StoryMessageSequenceTest extends TestCase
{
    public function testMash() : void
    {
        $mashed = StoryMessageSequence::mash(
            new TextMessage('hey'),
            new StoryMessageSequence(
                new TextMessage('one', 'two'),
                new TextMessage('three')
            ),
            new TextMessage('bye')
        );

        $this->assertCount(
            4,
            $mashed->messages()
        );
    }

    /**
     * @dataProvider finalizedMergeProvider
     */
    public function testFinalizedMerge(
        StoryMessageSequence $base,
        StoryMessageSequence $added,
        bool $expected
    ) : void
    {
        $this->assertEquals(
            $expected,
            $base->merge($added)->isFinalized()
        );
    }

    public function finalizedMergeProvider() : array
    {
        return [
            [
                StoryMessageSequence::empty(),
                StoryMessageSequence::empty()->finalize(),
                true
            ],
            [
                StoryMessageSequence::empty()->finalize(),
                StoryMessageSequence::empty(),
                false
            ],
        ];
    }
}
