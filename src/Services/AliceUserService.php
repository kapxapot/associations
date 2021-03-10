<?php

namespace App\Services;

use App\Models\AliceUser;
use App\Models\DTO\AliceRequest;
use App\Repositories\Interfaces\AliceUserRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Webmozart\Assert\Assert;

class AliceUserService
{
    private AliceUserRepositoryInterface $aliceUserRepository;
    private UserRepositoryInterface $userRepository;

    public function __construct(
        AliceUserRepositoryInterface $aliceUserRepository,
        UserRepositoryInterface $userRepository
    )
    {
        $this->aliceUserRepository = $aliceUserRepository;
        $this->userRepository = $userRepository;
    }

    public function getOrCreateAliceUser(AliceRequest $request): AliceUser
    {
        $aliceUserId = $request->userId();

        Assert::stringNotEmpty($aliceUserId);

        $aliceUser = $this->aliceUserRepository->getByAliceId($aliceUserId);

        if ($aliceUser === null) {
            $aliceUser = $this->aliceUserRepository->store(
                [
                    'alice_id' => $aliceUserId,
                ]
            );
        }

        Assert::notNull($aliceUser);

        return $this->ensureAliceUserIsValid($aliceUser);
    }

    /**
     * Checks that the Alice user has a corresponding local user.
     */
    private function ensureAliceUserIsValid(AliceUser $aliceUser): AliceUser
    {
        if ($aliceUser->isValid()) {
            return $aliceUser;
        }

        $user = $this->userRepository->store(
            [
                'login' => '',
                'password' => '',
                'age' => 0,
            ]
        );

        Assert::notNull($user);

        $aliceUser->userId = $user->getId();

        return $this->aliceUserRepository->save($aliceUser);
    }
}
