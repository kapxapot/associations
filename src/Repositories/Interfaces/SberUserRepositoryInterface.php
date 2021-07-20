<?php

namespace App\Repositories\Interfaces;

use App\Models\SberUser;
use App\Models\User;
use Plasticode\Repositories\Interfaces\Generic\ChangingRepositoryInterface;

interface SberUserRepositoryInterface extends ChangingRepositoryInterface
{
    public function get(?int $id): ?SberUser;

    public function getBySberId(string $id): ?SberUser;

    public function getByUser(User $user): ?SberUser;

    public function save(SberUser $user): SberUser;

    public function store(array $data): SberUser;
}
