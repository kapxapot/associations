<?php

namespace App\Repositories;

use App\Models\SberUser;
use App\Models\User;
use App\Repositories\Generic\Repository;
use App\Repositories\Interfaces\SberUserRepositoryInterface;

class SberUserRepository extends Repository implements SberUserRepositoryInterface
{
    protected function entityClass(): string
    {
        return SberUser::class;
    }

    public function get(?int $id): ?SberUser
    {
        return $this->getEntity($id);
    }

    public function getBySberId(string $id): ?SberUser
    {
        return $this->query()->where('sber_id', $id)->one();
    }

    public function getByUser(User $user): ?SberUser
    {
        return $this
            ->query()
            ->where('user_id', $user->getId())
            ->one();
    }

    public function save(SberUser $user): SberUser
    {
        return $this->saveEntity($user);
    }

    public function store(array $data): SberUser
    {
        return $this->storeEntity($data);
    }
}
