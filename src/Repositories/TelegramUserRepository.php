<?php

namespace App\Repositories;

use App\Models\TelegramUser;
use App\Repositories\Interfaces\TelegramUserRepositoryInterface;
use Plasticode\Repositories\Idiorm\Basic\IdiormRepository;

class TelegramUserRepository extends IdiormRepository implements TelegramUserRepositoryInterface
{
    protected string $entityClass = TelegramUser::class;

    public function get(?int $id) : ?TelegramUser
    {
        return $this->getEntity($id);
    }

    public function getByTelegramId(int $id) : ?TelegramUser
    {
        return $this->query()->where('telegram_id', $id)->one();
    }

    public function save(TelegramUser $user) : TelegramUser
    {
        return $this->saveEntity($user);
    }

    public function store(array $data) : TelegramUser
    {
        return $this->storeEntity($data);
    }
}
