<?php

namespace App\Validation\Rules;

use App\Models\Association;
use Respect\Validation\Rules\AbstractRule;

class AssociationExists extends AbstractRule
{
    public function validate($input)
    {
        return Association::get($input) !== null;
    }
}
