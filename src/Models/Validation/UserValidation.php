<?php

namespace App\Models\Validation;

use Plasticode\Models\Validation\UserValidation as BaseUserValidation;

class UserValidation extends BaseUserValidation
{
    public function getRules(array $data, $id = null) : array
    {
        $rules = parent::getRules($data, $id);

        $rules['age'] = $this->rule('posInt');

        return $rules;
    }
}
