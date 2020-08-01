<?php

namespace App\Models\Validation;

use Plasticode\Models\Validation\UserValidation as BaseUserValidation;
use Plasticode\Repositories\Interfaces\UserRepositoryInterface;
use Plasticode\Validation\ValidationRules;

class UserValidation extends BaseUserValidation
{
    private AgeValidation $ageValidation;

    public function __construct(
        ValidationRules $validationRules,
        AgeValidation $ageValidation,
        UserRepositoryInterface $userRepository
    )
    {
        parent::__construct(
            $validationRules,
            $userRepository
        );

        $this->ageValidation = $ageValidation;
    }

    public function getRules(array $data, $id = null) : array
    {
        return array_merge(
            parent::getRules($data, $id),
            $this->ageValidation->getRules($data, $id)
        );
    }
}
