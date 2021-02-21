<?php

namespace App\Repositories;

use App\Models\AliceUser;
use App\Models\User;
use App\Repositories\Interfaces\AliceUserRepositoryInterface;
use Plasticode\Repositories\Idiorm\Generic\IdiormRepository;

class AliceUserRepository extends IdiormRepository implements AliceUserRepositoryInterface
{
    protected function entityClass(): string
    {
        return AliceUser::class;
    }

    public function get(?int $id): ?AliceUser
    {
        return $this->getEntity($id);
    }

    public function getByAliceId(string $id): ?AliceUser
    {
        return $this->query()->where('alice_id', $id)->one();
    }

    public function getByUser(User $user): ?AliceUser
    {
        return $this
            ->query()
            ->where('user_id', $user->getId())
            ->one();
    }

    public function save(AliceUser $user): AliceUser
    {
        return $this->saveEntity($user);
    }

    public function store(array $data): AliceUser
    {
        return $this->storeEntity($data);
    }
}
