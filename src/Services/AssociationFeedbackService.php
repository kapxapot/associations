<?php

namespace App\Services;

use App\Models\Association;
use App\Models\AssociationFeedback;
use App\Models\User;
use Plasticode\Interfaces\SettingsProviderInterface;
use Plasticode\Util\Convert;
use Plasticode\Util\Date;
use Plasticode\Validation\Interfaces\ValidatorInterface;
use Plasticode\Validation\ValidationRules;

class AssociationFeedbackService
{
    /** @var SettingsProviderInterface */
    private $settingsProvider;

    /** @var ValidatorInterface */
    private $validator;

    public function __construct(
        SettingsProviderInterface $settingsProvider,
        ValidatorInterface $validator
    )
    {
        $this->settingsProvider = $settingsProvider;
        $this->validator = $validator;
    }

    public function toModel(array $data, User $user) : AssociationFeedback
    {
        $this->validate($data);

        return $this->convertToModel($data, $user);
    }

    private function convertToModel(array $data, User $user) : AssociationFeedback
    {
        $associationId = $data['association_id'];
        $association = Association::get($associationId);
        
        $model =
            AssociationFeedback::getByAssociationAndUser($association, $user)
            ??
            AssociationFeedback::create(
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
        $rules = new ValidationRules($this->settingsProvider);

        return [
            'association_id' => $rules->get('posInt')
                ->associationExists(),
        ];
    }
}
