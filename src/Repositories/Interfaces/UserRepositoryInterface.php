<?php

namespace App\Repositories\Interfaces;

use App\Models\User;
use Plasticode\Repositories\Interfaces\UserRepositoryInterface as BaseUserRepositoryInterface;

interface UserRepositoryInterface extends BaseUserRepositoryInterface
{
    function get(?int $id) : ?User;
    function store(array $data) : User;
}
