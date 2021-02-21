<?php

namespace App\Repositories\Interfaces;

use App\Models\AliceUser;
use App\Models\User;
use Plasticode\Repositories\Interfaces\Generic\ChangingRepositoryInterface;

interface AliceUserRepositoryInterface extends ChangingRepositoryInterface
{
    function get(?int $id): ?AliceUser;
    function getByAliceId(string $id): ?AliceUser;
    function getByUser(User $user): ?AliceUser;
    function save(AliceUser $user): AliceUser;
    function store(array $data): AliceUser;
}
