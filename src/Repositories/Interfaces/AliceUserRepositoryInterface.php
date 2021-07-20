<?php

namespace App\Repositories\Interfaces;

use App\Models\AliceUser;
use App\Models\User;
use Plasticode\Repositories\Interfaces\Generic\ChangingRepositoryInterface;

interface AliceUserRepositoryInterface extends ChangingRepositoryInterface
{
    public function get(?int $id): ?AliceUser;

    public function getByAliceId(string $id): ?AliceUser;

    public function getByUser(User $user): ?AliceUser;

    public function save(AliceUser $user): AliceUser;

    public function store(array $data): AliceUser;
}
