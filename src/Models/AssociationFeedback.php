<?php

namespace App\Models;

use Webmozart\Assert\Assert;

/**
 * @property integer $associationId
 */
class AssociationFeedback extends Feedback
{
    protected ?Association $association = null;

    private bool $associationInitialized = false;

    public function association() : Association
    {
        Assert::true($this->associationInitialized);

        return $this->association;
    }

    public function withAssociation(Association $association) : self
    {
        $this->association = $association;
        $this->associationInitialized = true;

        return $this;
    }
}
