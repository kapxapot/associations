<?php

namespace App\Testing\Mocks\Repositories;

use App\Collections\TelegramUserCollection;
use App\Models\TelegramUser;
use App\Models\User;
use App\Repositories\Interfaces\TelegramUserRepositoryInterface;
use Plasticode\Testing\Mocks\Repositories\Generic\RepositoryMock;
use Plasticode\Testing\Seeders\Interfaces\ArraySeederInterface;

class TelegramUserRepositoryMock extends RepositoryMock implements TelegramUserRepositoryInterface
{
    private static int $num = 0;
    private TelegramUserCollection $users;

    public function __construct(?ArraySeederInterface $seeder = null)
    {
        if (self::$num++ == 1) {
            throw new \Exception('Mock created more than once.');
        }

        $this->users = $seeder
            ? TelegramUserCollection::make($seeder->seed())
            : TelegramUserCollection::empty();
    }

    public function get(?int $id): ?TelegramUser
    {
        return $this->users->first('id', $id);
    }

    public function getByTelegramId(int $id): ?TelegramUser
    {
        return $this->users->first(
            fn (TelegramUser $u) => $u->telegramId == $id
        );
    }

    public function getByUser(User $user): ?TelegramUser
    {
        return $this->users->first(
            fn (TelegramUser $u) => $u->userId == $user->getId()
        );
    }

    public function save(TelegramUser $user): TelegramUser
    {
        if ($this->users->contains($user)) {
            return $user;
        }

        if (!$user->isPersisted()) {
            $user->id = $this->users->nextId();
        }

        $this->users = $this->users->add($user);

        return $user;
    }

    public function store(array $data): TelegramUser
    {
        $user = TelegramUser::create($data);

        return $this->save($user);
    }
}
