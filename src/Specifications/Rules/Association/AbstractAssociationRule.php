<?php

namespace App\Specifications\Rules\Association;

use App\Models\Association;
use App\Specifications\Rules\AbstractRule;
use Plasticode\Models\Generic\DbModel;

abstract class AbstractAssociationRule extends AbstractRule
{
    /**
     * @param Association $model
     */
    public function check(DbModel $model): bool
    {
        return $this->checkAssociation($model);
    }

    abstract protected function checkAssociation(Association $association): bool;
}
