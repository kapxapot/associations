<?php

namespace App\Services;

use App\Models\AssociationFeedback;
use App\Models\User;
use App\Repositories\Interfaces\AssociationFeedbackRepositoryInterface;
use App\Repositories\Interfaces\AssociationRepositoryInterface;
use Plasticode\Util\Convert;
use Plasticode\Util\Date;
use Plasticode\Validation\Interfaces\ValidatorInterface;
use Plasticode\Validation\ValidationRules;

class AssociationFeedbackService
{
    private AssociationRepositoryInterface $associationRepository;
    private AssociationFeedbackRepositoryInterface $associationFeedbackRepository;
    private ValidatorInterface $validator;
    private ValidationRules $validationRules;

    public function __construct(
        AssociationRepositoryInterface $associationRepository,
        AssociationFeedbackRepositoryInterface $associationFeedbackRepository,
        ValidatorInterface $validator,
        ValidationRules $validationRules
    )
    {
        $this->associationRepository = $associationRepository;
        $this->associationFeedbackRepository = $associationFeedbackRepository;
        $this->validator = $validator;
        $this->validationRules = $validationRules;
    }

    public function toModel(array $data, User $user) : AssociationFeedback
    {
        $this->validate($data);

        return $this->convertToModel($data, $user);
    }

    private function convertToModel(array $data, User $user) : AssociationFeedback
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

    private function validate(array $data)
    {
        $rules = $this->getRules($data);
        
        $this
            ->validator
            ->validateArray($data, $rules)
            ->throwOnFail();
    }

    private function getRules(array $data) : array
    {
        return [
            'association_id' => $this
                ->validationRules
                ->get('posInt')
                ->associationExists($this->associationRepository)
        ];
    }
}
