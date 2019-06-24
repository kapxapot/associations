<?php

namespace App\Generators;

use Plasticode\Generators\UsersGenerator as UsersGeneratorBase;

class UsersGenerator extends UsersGeneratorBase
{
    public function getRules($data, $id = null)
    {
        $rules = parent::getRules($data, $id);

        $rules['age'] = $this->rule('posInt');
        
        return $rules;
    }
}
