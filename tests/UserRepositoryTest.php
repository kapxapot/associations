<?php

namespace App\Tests;

use App\Models\User;

final class UserRepositoryTest extends BaseTestCase
{
    public function testRepositoryExists() : void
    {
        $repo = $this->container->userRepository;
        $this->assertNotNull($repo);
    }

    /** @dataProvider getUserProvider */
    public function testGetUser(int $id) : void
    {
        $user = $this->container->userRepository->get($id);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($id, $user->getId());
    }

    public function getUserProvider()
    {
        return [
            [parent::DEFAULT_USER_ID],
        ];
    }
}