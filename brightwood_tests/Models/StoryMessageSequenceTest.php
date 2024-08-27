<?php

namespace Brightwood\Tests\Models;

use Brightwood\Models\Data\StoryData;
use Brightwood\Models\Messages\StoryMessage;
use Brightwood\Models\Messages\StoryMessageSequence;
use Brightwood\Models\Messages\TextMessage;
use PHPUnit\Framework\TestCase;

final class StoryMessageSequenceTest extends TestCase
{
    public function testMash(): void
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
    ): void
    {
        $this->assertEquals(
            $expected,
            $base->merge($added)->isFinalized()
        );
    }

    public function finalizedMergeProvider(): array
    {
        return [
            [
                StoryMessageSequence::empty(),
                StoryMessageSequence::makeFinalized(),
                true
            ],
            [
                StoryMessageSequence::makeFinalized(),
                StoryMessageSequence::empty(),
                false
            ],
        ];
    }

    /**
     * @dataProvider hasTextProvider
     */
    public function testHasText(StoryMessageSequence $sequence, bool $expected): void
    {
        $this->assertEquals($expected, $sequence->hasText());
    }

    public function hasTextProvider(): array
    {
        return [
            'no messages' => [
                StoryMessageSequence::make(),
                false
            ],
            'no lines' => [
                StoryMessageSequence::make(
                    new StoryMessage(0)
                ),
                false
            ],
            'empty lines' => [
                StoryMessageSequence::make(
                    new StoryMessage(0, [])
                ),
                false
            ],
            'empty string' => [
                StoryMessageSequence::make(
                    new StoryMessage(0, [''])
                ),
                false
            ],
            'good string' => [
                StoryMessageSequence::make(
                    new StoryMessage(0, ['text'])
                ),
                true
            ]
        ];
    }

    public function testMergeMergesActions()
    {
        $sequence1 = StoryMessageSequence::empty()
            ->withActions('one');

        $sequence2 = StoryMessageSequence::empty()
            ->withActions('two');

        $merged = $sequence1->merge($sequence2);
        $actions = $merged->actions();

        $this->assertCount(1, $actions);
        $this->assertEquals('two', $actions[0]);
    }

    public function testMergeMergesData()
    {
        $sequence1 = StoryMessageSequence::empty()
            ->withData(new StoryData([
                'first' => 1,
                'second' => 2,
            ]));

        $sequence2 = StoryMessageSequence::empty()
            ->withData(new StoryData([
                'second' => 10,
                'third' => 3,
            ]));

        $merged = $sequence1->merge($sequence2);

        $this->assertInstanceOf(StoryData::class, $merged->data());

        $data = $merged->data()->toArray();

        $this->assertCount(2, $data);
        $this->assertEquals(10, $data['second']);
        $this->assertEquals(3, $data['third']);
    }

    public function testMergeMergesVars()
    {
        $sequence1 = StoryMessageSequence::empty()
            ->withVars([
                'first' => 1,
                'second' => 2,
            ]);

        $sequence2 = StoryMessageSequence::empty()
            ->withVars([
                'second' => 10,
                'third' => 3,
            ]);

        $merged = $sequence1->merge($sequence2);
        $vars = $merged->vars();

        $this->assertCount(3, $vars);
        $this->assertEquals(1, $vars['first']);
        $this->assertEquals(10, $vars['second']);
        $this->assertEquals(3, $vars['third']);
    }

    /**
     * @dataProvider mergeStageProvider
     */
    public function testMergeMergesStage(?string $stage1, ?string $stage2, ?string $expected)
    {
        $sequence1 = StoryMessageSequence::empty();
        $sequence2 = StoryMessageSequence::empty();

        if ($stage1) {
            $sequence1->withStage($stage1);
        }

        if ($stage2) {
            $sequence2->withStage($stage2);
        }

        $merged = $sequence1->merge($sequence2);
        $stage = $merged->stage();

        $this->assertEquals($expected, $stage);
    }

    public function mergeStageProvider(): array
    {
        return [
            [null, null, null],
            ['stage1', null, 'stage1'],
            [null, 'stage2', 'stage2'],
            ['stage1', 'stage2', 'stage2'],
        ];
    }
}
