<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Plasticode\Repositories\Idiorm\UserRepository as BaseUserRepository;

class UserRepository extends BaseUserRepository implements UserRepositoryInterface
{
    public function get(?int $id) : ?User
    {
        return $this->getEntity($id);
    }
}
