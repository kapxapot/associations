<?php

namespace App\Testing\Mocks\Repositories;

use App\Models\TelegramUser;
use App\Models\User;
use App\Repositories\Interfaces\TelegramUserRepositoryInterface;
use Plasticode\Collections\Basic\DbModelCollection;
use Plasticode\Exceptions\InvalidOperationException;
use Plasticode\Testing\Seeders\Interfaces\ArraySeederInterface;

class TelegramUserRepositoryMock implements TelegramUserRepositoryInterface
{
    private DbModelCollection $users;

    public function __construct(
        ArraySeederInterface $seeder
    )
    {
        $this->users = DbModelCollection::make($seeder->seed());
    }

    public function get(?int $id) : ?TelegramUser
    {
        return $this->users->first('id', $id);
    }

    public function getByTelegramId(int $id) : ?TelegramUser
    {
        return $this->users->first(
            fn (TelegramUser $u) => $u->telegramId == $id
        );
    }

    public function getByUser(User $user) : ?TelegramUser
    {
        return $this->users->first(
            fn (TelegramUser $u) => $u->userId == $user->getId()
        );
    }

    public function save(TelegramUser $user) : TelegramUser
    {
        // placeholder
        throw new InvalidOperationException();
    }

    public function store(array $data) : TelegramUser
    {
        // placeholder
        throw new InvalidOperationException();
    }
}
