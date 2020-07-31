<?php

namespace App\Testing\Mocks\Repositories;

use App\Collections\UserCollection;
use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Plasticode\Models\User as BaseUser;
use Plasticode\Testing\Seeders\Interfaces\ArraySeederInterface;

class UserRepositoryMock implements UserRepositoryInterface
{
    private UserCollection $users;

    public function __construct(
        ArraySeederInterface $seeder
    )
    {
        $this->users = UserCollection::make($seeder->seed());
    }

    public function get(?int $id) : ?User
    {
        return $this->users->first('id', $id);
    }

    /**
     * @return User
     */
    public function create(array $data) : BaseUser
    {
        return User::create($data);
    }

    /**
     * @param User $user
     * @return User
     */
    public function save(BaseUser $user) : BaseUser
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

    public function store(array $data) : User
    {
        $user = $this->create($data);

        return $this->save($user);
    }

    /**
     * @return User|null
     */
    public function getByLogin(string $login) : ?BaseUser
    {
        return $this->users->first('login', $login);
    }
}
