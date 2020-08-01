<?php

namespace App\Models\Validation;

use Plasticode\Models\Validation\Validation;

class AgeValidation extends Validation
{
    public function getRules(array $data, $id = null) : array
    {
        return [
            'age' => $this->rule('posInt')
        ];
    }
}
