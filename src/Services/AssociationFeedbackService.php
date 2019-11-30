<?php

namespace App\Services;

use App\Models\Association;
use App\Models\AssociationFeedback;
use App\Models\User;
use Plasticode\Contained;
use Plasticode\Exceptions\ValidationException;
use Plasticode\Util\Convert;
use Plasticode\Util\Date;
use Plasticode\Validation\ValidationRules;

class AssociationFeedbackService extends Contained
{
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
        $validation = $this->validator->validateArray($data, $rules);
        
        if ($validation->failed()) {
            throw new ValidationException($validation->errors);
        }
    }
    
    private function getRules(array $data) : array
    {
        $rules = new ValidationRules($this->container);

        return [
            'association_id' => $rules->get('posInt')
                ->associationExists(),
        ];
    }
}
