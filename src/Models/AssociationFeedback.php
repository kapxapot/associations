<?php

namespace App\Models;

class AssociationFeedback extends Feedback
{
    public function association() : Association
    {
        return Association::get($this->associationId);
    }
}
