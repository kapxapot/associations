<?php

namespace App\Collections;

use App\Models\AssociationOverride;

class AssociationOverrideCollection extends OverrideCollection
{
    protected string $class = AssociationOverride::class;

    public function latest(): ?AssociationOverride
    {
        return parent::latest();
    }
}
