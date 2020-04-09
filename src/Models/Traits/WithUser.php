<?php

namespace App\Models\Traits;

use App\Models\User;
use Webmozart\Assert\Assert;

/**
 * @property integer $userId
 */
trait WithUser
{
    protected ?User $user = null;

    private bool $userInitialized = false;

    public function user() : ?User
    {
        Assert::true($this->userInitialized);

        return $this->user;
    }

    public function withUser(?User $user) : self
    {
        $this->user = $user;
        $this->userInitialized = true;

        return $this;
    }
}
