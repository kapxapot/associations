<?php

namespace App\Generators;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Plasticode\Generators\Core\GeneratorContext;
use Plasticode\Generators\UserGenerator as BaseUserGenerator;
use Plasticode\Models\Validation\UserValidation;

class UserGenerator extends BaseUserGenerator
{
    public function __construct(
        GeneratorContext $context,
        UserRepositoryInterface $userRepository,
        UserValidation $userValidation
    )
    {
        parent::__construct(
            $context,
            $userRepository,
            $userValidation
        );
    }

    protected function entityClass(): string
    {
        return User::class;
    }

    protected function getRepository(): UserRepositoryInterface
    {
        return parent::getRepository();
    }

    public function afterLoad(array $item): array
    {
        $item = parent::afterLoad($item);

        $id = $item[$this->idField()];

        $user = $this->getRepository()->get($id);

        $item['display_name'] = $user->isTelegramUser()
            ? $user->telegramUser()->fullName()
            : $user->displayName();

        $item['telegram'] = $user->isTelegramUser()
            ? $user->telegramUser()->publicName()
            : null;

        $item['gender'] = $user->gender();

        return $item;
    }
}
