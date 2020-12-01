<?php

namespace App\Models\Validation;

use Plasticode\Validation\Validation;

class AgeValidation extends Validation
{
    public function getRules(array $data, $id = null) : array
    {
        return [
            'age' => $this->rule('posInt')
        ];
    }
}
