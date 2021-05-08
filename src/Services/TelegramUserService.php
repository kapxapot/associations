<?php

namespace App\Services;

use App\Repositories\Interfaces\UserRepositoryInterface;
use Plasticode\Models\TelegramUser;
use Plasticode\Repositories\Interfaces\TelegramUserRepositoryInterface;
use Webmozart\Assert\Assert;

class TelegramUserService
{
    private TelegramUserRepositoryInterface $telegramUserRepository;
    private UserRepositoryInterface $userRepository;

    public function __construct(
        TelegramUserRepositoryInterface $telegramUserRepository,
        UserRepositoryInterface $userRepository
    )
    {
        $this->telegramUserRepository = $telegramUserRepository;
        $this->userRepository = $userRepository;
    }

    public function getOrCreateTelegramUser(array $data): TelegramUser
    {
        $tgUserId = $data['id'] ?? null;

        Assert::notNull($tgUserId);

        $tgUser = $this->telegramUserRepository->getByTelegramId($tgUserId);

        if ($tgUser === null) {
            $tgUser = $this->telegramUserRepository->store([
                'telegram_id' => $tgUserId,
                'username' => $data['username'] ?? null,
                'first_name' => $data['first_name'] ?? null,
                'last_name' => $data['last_name'] ?? null,
            ]);
        }

        Assert::notNull($tgUser);

        return $this->ensureTelegramUserIsValid($tgUser);
    }

    /**
     * Checks that the Telegram user has a corresponding local user.
     */
    private function ensureTelegramUserIsValid(TelegramUser $tgUser): TelegramUser
    {
        if ($tgUser->isValid()) {
            return $tgUser;
        }

        $user = $this->userRepository->store([
            'login' => '',
            'password' => '',
            'age' => 0,
        ]);

        Assert::notNull($user);

        $tgUser->userId = $user->getId();

        return $this->telegramUserRepository->save($tgUser);
    }
}
