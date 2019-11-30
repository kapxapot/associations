<?php

namespace App\Tests;

use App\Models\Association;
use App\Models\AssociationFeedback;
use Plasticode\Exceptions\ValidationException;

final class AssociationFeedbackTest extends BaseTestCase
{
    /** @dataProvider toModelProvider */
    public function testToModel(array $data, array $expected) : void
    {
        $service = $this->container->associationFeedbackService;
        $user = $this->getDefaultUser();

        $model = $service->toModel($data, $user);

        $this->assertInstanceOf(AssociationFeedback::class, $model);
        $this->assertInstanceOf(Association::class, $model->association());

        $this->assertEquals(
            $expected,
            [
                $model->association()->getId(),
                $model->dislike,
                $model->mature
            ]
        );
    }

    public function toModelProvider()
    {
        return [
            [
                [
                    'association_id' => 1,
                    'dislike' => 'true',
                    'mature' => 'true',
                ],
                [1, 1, 1]
            ],
            [
                [
                    'association_id' => 1,
                ],
                [1, 0, 0]
            ],
        ];
    }

    public function testInvalidData(): void
    {
        $this->expectException(ValidationException::class);

        $service = $this->container->associationFeedbackService;
        $user = $this->getDefaultUser();

        $service->toModel([], $user);
    }
}
