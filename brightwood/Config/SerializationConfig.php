<?php

namespace Brightwood\Config;

use App\Repositories\Interfaces\TelegramUserRepositoryInterface;
use Brightwood\Models\Cards\Players\Bot;
use Brightwood\Models\Cards\Players\FemaleBot;
use Brightwood\Models\Cards\Players\Human;
use Brightwood\Models\Cards\Restrictions\SuitRestriction;
use Brightwood\Models\Cards\Sets\Hand;
use Brightwood\Serialization\Serializers\BotSerializer;
use Brightwood\Serialization\Serializers\FemaleBotSerializer;
use Brightwood\Serialization\Serializers\HandSerializer;
use Brightwood\Serialization\Serializers\HumanSerializer;
use Brightwood\Serialization\Serializers\SuitRestrictionSerializer;
use Brightwood\Serialization\SerializerSource;

class SerializationConfig extends SerializerSource
{
    public function __construct(
        TelegramUserRepositoryInterface $telegramUserRepository
    )
    {
        return parent::__construct(
            [
                Bot::class => new BotSerializer(),
                FemaleBot::class => new FemaleBotSerializer(),
                Human::class => new HumanSerializer(
                    $telegramUserRepository
                ),
                Hand::class => new HandSerializer(),
                SuitRestriction::class => new SuitRestrictionSerializer(),
            ]
        );
    }
}
