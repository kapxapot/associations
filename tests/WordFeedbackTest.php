<?php

namespace App\Tests;

use App\Models\Word;
use App\Models\WordFeedback;
use Plasticode\Exceptions\ValidationException;

final class WordFeedbackTest extends BaseTestCase
{
    /** @dataProvider toModelProvider */
    public function testToModel(array $data, array $expected) : void
    {
        $service = $this->container->wordFeedbackService;
        $user = $this->getDefaultUser();

        $model = $service->toModel($data, $user);

        $this->assertInstanceOf(WordFeedback::class, $model);
        $this->assertInstanceOf(Word::class, $model->word());

        $duplicateId = is_null($model->duplicate())
            ? null
            : $model->duplicate()->getId();

        $this->assertEquals(
            $expected,
            [
                $model->word()->getId(),
                $model->dislike,
                $model->hasTypo(),
                $model->typo,
                $duplicateId,
                $model->mature
            ]
        );
    }

    public function toModelProvider()
    {
        return [
            [
                [
                    'word_id' => '1',
                    'dislike' => 'true',
                    'typo' => 'ababa',
                    'duplicate' => 'стул',
                    'mature' => 'true'
                ],
                [1, 1, true, 'ababa', 2, 1]
            ],
            [
                [
                    'word_id' => 1,
                ],
                [1, 0, false, null, null, 0]
            ],
        ];
    }

    public function testInvalidData() : void
    {
        $this->expectException(ValidationException::class);

        $service = $this->container->wordFeedbackService;
        $user = $this->getDefaultUser();

        $service->toModel([], $user);
    }
}
