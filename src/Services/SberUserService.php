<?php

namespace App\Services;

use App\Bots\SberRequest;
use App\Models\SberUser;
use App\Repositories\Interfaces\SberUserRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Webmozart\Assert\Assert;

class SberUserService
{
    private SberUserRepositoryInterface $sberUserRepository;
    private UserRepositoryInterface $userRepository;

    public function __construct(
        SberUserRepositoryInterface $sberUserRepository,
        UserRepositoryInterface $userRepository
    )
    {
        $this->sberUserRepository = $sberUserRepository;
        $this->userRepository = $userRepository;
    }

    public function getOrCreateSberUser(SberRequest $request, string $sberUserId): SberUser
    {
        $sberUser =
            $this->sberUserRepository->getBySberId($sberUserId)
            ?? $this->sberUserRepository->store(['sber_id' => $sberUserId]);

        Assert::notNull($sberUser);

        return $this->ensureSberUserIsValid($sberUser);
    }

    /**
     * Checks that the Sber user has a corresponding local user.
     */
    private function ensureSberUserIsValid(SberUser $sberUser): SberUser
    {
        if ($sberUser->isValid()) {
            return $sberUser;
        }

        $user = $this->userRepository->store([
            'login' => '',
            'password' => '',
            'age' => 0,
        ]);

        Assert::notNull($user);

        $sberUser->userId = $user->getId();

        return $this->sberUserRepository->save($sberUser);
    }
}
