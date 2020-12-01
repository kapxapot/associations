<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Plasticode\Repositories\Idiorm\UserRepository as BaseUserRepository;

class UserRepository extends BaseUserRepository implements UserRepositoryInterface
{
    protected function entityClass() : string
    {
        return User::class;
    }

    public function get(?int $id) : ?User
    {
        return $this->getEntity($id);
    }

    public function store(array $data) : User
    {
        return $this->storeEntity($data);
    }
}
