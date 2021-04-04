<?php

namespace App\Policies;

use Plasticode\Auth\Access;
use Plasticode\Data\Rights;
use Plasticode\Models\User;

class UserPolicyBuilder
{
    private Access $access;

    public function __construct(
        Access $access
    )
    {
        $this->access = $access;
    }

    public function buildFor(User $user): UserPolicy
    {
        $policy = new UserPolicy();

        return $policy
            ->withCanSeeAllGames(
                $this->access->checkActionRights('games', Rights::READ, $user)
            )
            ->withCanSeeAllWords(
                $this->access->checkActionRights('words', Rights::READ, $user)
            )
            ->withCanSeeAllWords(
                $this->access->checkActionRights('associations', Rights::READ, $user)
            );
    }
}
