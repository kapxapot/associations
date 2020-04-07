<?php

namespace App\Repositories\Traits;

use App\Models\User;
use Plasticode\Query;

trait ByUserRepository
{
    protected string $userIdField = 'user_id';

    protected function filterByUser(Query $query, User $user) : Query
    {
        return $query->where($this->userIdField, $user->getId());
    }
}
