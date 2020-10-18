<?php

namespace Brightwood\Serialization\Cards\Serializers\Players;

use App\Repositories\Interfaces\TelegramUserRepositoryInterface;
use Brightwood\Models\Cards\Players\Human;
use Brightwood\Serialization\Cards\Interfaces\RootDeserializerInterface;
use Webmozart\Assert\Assert;

class HumanSerializer extends PlayerSerializer
{
    private TelegramUserRepositoryInterface $telegramUserRepository;

    public function __construct(
        TelegramUserRepositoryInterface $telegramUserRepository
    )
    {
        $this->telegramUserRepository = $telegramUserRepository;
    }

    /**
     * @param Human $obj
     */
    public function deserialize(
        RootDeserializerInterface $rootDeserializer,
        object $obj,
        array $data
    ) : Human
    {
        /** @var Human */
        $obj = parent::deserialize($rootDeserializer, $obj, $data);

        $tgUser = $this->telegramUserRepository->get($data['telegram_user_id']);

        Assert::notNull($tgUser);

        return $obj->withTelegramUser($tgUser);
    }
}
