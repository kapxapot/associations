<?php

namespace App\Tests\Models;

use App\Collections\AssociationFeedbackCollection;
use App\Hydrators\AssociationFeedbackHydrator;
use App\Models\Association;
use App\Models\AssociationFeedback;
use App\Repositories\Interfaces\AssociationFeedbackRepositoryInterface;
use App\Repositories\Interfaces\AssociationRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Services\AssociationFeedbackService;
use App\Testing\Factories\UserRepositoryFactory;
use App\Testing\Mocks\Repositories\AssociationFeedbackRepositoryMock;
use App\Testing\Mocks\Repositories\AssociationRepositoryMock;
use App\Tests\IntegrationTest;
use Plasticode\Events\EventDispatcher;
use Plasticode\Exceptions\ValidationException;
use Plasticode\ObjectProxy;
use Plasticode\Settings\SettingsProvider;
use Plasticode\Validation\ValidationRules;
use Plasticode\Validation\Validator;

final class AssociationFeedbackTest extends IntegrationTest
{
    private AssociationFeedbackRepositoryInterface $associationFeedbackRepository;
    private AssociationRepositoryInterface $associationRepository;
    private UserRepositoryInterface $userRepository;

    private AssociationFeedbackService $associationFeedbackService;

    public function setUp() : void
    {
        parent::setUp();

        $this->userRepository = UserRepositoryFactory::make();
        $this->associationRepository = new AssociationRepositoryMock();

        $this->associationRepository->save(
            Association::create(['id' => 1])->withFeedbacks(
                AssociationFeedbackCollection::empty()
            )
        );

        $this->associationFeedbackRepository = new AssociationFeedbackRepositoryMock(
            new ObjectProxy(
                fn () => new AssociationFeedbackHydrator(
                    $this->associationRepository,
                    $this->userRepository
                )
            )
        );

        $this->associationFeedbackService = new AssociationFeedbackService(
            $this->associationFeedbackRepository,
            $this->associationRepository,
            new Validator(),
            new ValidationRules(
                new SettingsProvider($this->settings)
            ),
            new EventDispatcher()
        );
    }

    public function tearDown() : void
    {
        unset($this->associationFeedbackService);

        unset($this->associationFeedbackRepository);
        unset($this->associationRepository);
        unset($this->userRepository);

        parent::tearDown();
    }

    /**
     * @dataProvider toModelProvider
     */
    public function testToModel(array $data, array $expected) : void
    {
        $user = $this->userRepository->get(1);

        $model = $this->associationFeedbackService->toModel($data, $user);

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

    public function testInvalidData() : void
    {
        $this->expectException(ValidationException::class);

        $user = $this->userRepository->get(1);

        $this->associationFeedbackService->toModel([], $user);
    }
}
