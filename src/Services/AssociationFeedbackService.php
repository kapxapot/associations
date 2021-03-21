<?php

namespace App\Services;

use App\Events\Feedback\AssociationFeedbackCreatedEvent;
use App\Models\AssociationFeedback;
use App\Models\User;
use App\Repositories\Interfaces\AssociationFeedbackRepositoryInterface;
use App\Repositories\Interfaces\AssociationRepositoryInterface;
use Plasticode\Events\EventDispatcher;
use Plasticode\Util\Convert;
use Plasticode\Util\Date;
use Plasticode\Validation\Interfaces\ValidatorInterface;
use Plasticode\Validation\ValidationRules;

class AssociationFeedbackService
{
    private AssociationFeedbackRepositoryInterface $associationFeedbackRepository;
    private AssociationRepositoryInterface $associationRepository;

    private ValidatorInterface $validator;
    private ValidationRules $validationRules;

    private EventDispatcher $eventDispatcher;

    public function __construct(
        AssociationFeedbackRepositoryInterface $associationFeedbackRepository,
        AssociationRepositoryInterface $associationRepository,
        ValidatorInterface $validator,
        ValidationRules $validationRules,
        EventDispatcher $eventDispatcher
    )
    {
        $this->associationFeedbackRepository = $associationFeedbackRepository;
        $this->associationRepository = $associationRepository;

        $this->validator = $validator;
        $this->validationRules = $validationRules;

        $this->eventDispatcher = $eventDispatcher;
    }

    public function save(array $data, User $user): AssociationFeedback
    {
        $feedback = $this->toModel($data, $user);

        $feedback = $this
            ->associationFeedbackRepository
            ->save($feedback);

        $event = new AssociationFeedbackCreatedEvent($feedback);
        $this->eventDispatcher->dispatch($event);

        return $feedback;
    }

    public function toModel(array $data, User $user): AssociationFeedback
    {
        $this->validate($data);

        return $this->convertToModel($data, $user);
    }

    private function convertToModel(array $data, User $user): AssociationFeedback
    {
        $associationId = $data['association_id'];

        $association = $this
            ->associationRepository
            ->get($associationId);

        $model =
            $association->feedbackBy($user)
            ??
            $this->associationFeedbackRepository->create(
                [
                    'association_id' => $association->getId(),
                    'created_by' => $user->getId(),
                ]
            );

        $model->dislike = Convert::toBit($data['dislike'] ?? null);
        $model->mature = Convert::toBit($data['mature'] ?? null);

        if ($model->isPersisted()) {
            $model->updatedAt = Date::dbNow();
        }

        return $model;
    }

    private function validate(array $data): void
    {
        $rules = $this->getRules($data);

        $this
            ->validator
            ->validateArray($data, $rules)
            ->throwOnFail();
    }

    private function getRules(array $data): array
    {
        return [
            'association_id' => $this
                ->validationRules
                ->get('posInt')
                ->associationExists($this->associationRepository)
        ];
    }
}
