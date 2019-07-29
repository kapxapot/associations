<?php

namespace App\Generators;

use Plasticode\Generators\UsersGenerator as UsersGeneratorBase;

class UsersGenerator extends UsersGeneratorBase
{
    public function getRules(array $data, $id = null) : array
    {
        $rules = parent::getRules($data, $id);

        $rules['age'] = $this->rule('posInt');
        
        return $rules;
    }
}
