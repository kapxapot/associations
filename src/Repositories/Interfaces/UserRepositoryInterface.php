<?php

namespace App\Repositories\Interfaces;

use App\Models\User;
use Plasticode\Repositories\Interfaces\Generic\FilteringRepositoryInterface;
use Plasticode\Repositories\Interfaces\UserRepositoryInterface as BaseUserRepositoryInterface;

interface UserRepositoryInterface extends BaseUserRepositoryInterface, FilteringRepositoryInterface
{
    public function get(?int $id): ?User;

    public function store(array $data): User;
}
