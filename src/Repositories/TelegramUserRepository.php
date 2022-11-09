<?php

namespace App\Repositories;

use App\Models\TelegramUser;
use App\Models\User;
use App\Repositories\Generic\Repository;
use App\Repositories\Interfaces\TelegramUserRepositoryInterface;

class TelegramUserRepository extends Repository implements TelegramUserRepositoryInterface
{
    protected function entityClass(): string
    {
        return TelegramUser::class;
    }

    public function get(?int $id): ?TelegramUser
    {
        return $this->getEntity($id);
    }

    public function getByTelegramId(int $id): ?TelegramUser
    {
        return $this->query()->where('telegram_id', $id)->one();
    }

    public function getByUser(User $user): ?TelegramUser
    {
        return $this
            ->query()
            ->where('user_id', $user->getId())
            ->one();
    }

    public function save(TelegramUser $user): TelegramUser
    {
        $user->meta = $user->encodeMeta();

        return $this->saveEntity($user);
    }

    public function store(array $data): TelegramUser
    {
        return $this->storeEntity($data);
    }
}
