<?php

namespace App\Models\Validation\Factories;

use App\Models\Validation\AgeValidation;
use Plasticode\Models\Validation\UserValidation;
use Plasticode\Repositories\Interfaces\UserRepositoryInterface;
use Plasticode\Validation\ValidationRules;

class UserValidationFactory
{
    public function __invoke(
        AgeValidation $ageValidation,
        ValidationRules $validationRules,
        UserRepositoryInterface $userRepository
    ): UserValidation
    {
        $userValidation = new UserValidation($validationRules, $userRepository);
        
        return $userValidation->extendWith($ageValidation);
    }
}
