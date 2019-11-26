<?php

namespace App\Services;

use App\Models\Association;
use App\Models\AssociationFeedback;
use Plasticode\Contained;
use Plasticode\Exceptions\ValidationException;
use Plasticode\Util\Date;
use Plasticode\Validation\ValidationRules;

class AssociationFeedbackService extends Contained
{
    public function toModel(array $data) : AssociationFeedback
    {
        $this->validate($data);

        return $this->convertToModel($data);
    }

    private function convertToModel(array $data) : AssociationFeedback
    {
        $associationId = $data['association_id'];
        $association = Association::get($associationId);
        
        $user = $this->auth->getUser();
        
        $model =
            AssociationFeedback::getByAssociationAndUser($association, $user)
            ??
            AssociationFeedback::create(
                [
                    'association_id' => $association->getId(),
                    'created_by' => $user->getId(),
                ]
            );

        $model->dislike = toBit($data['dislike'] ?? null);
        $model->mature = toBit($data['mature'] ?? null);

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
