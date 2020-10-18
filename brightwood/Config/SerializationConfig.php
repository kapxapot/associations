<?php

namespace Brightwood\Config;

use App\Repositories\Interfaces\TelegramUserRepositoryInterface;
use Brightwood\Models\Cards\Players\Bot;
use Brightwood\Models\Cards\Players\FemaleBot;
use Brightwood\Models\Cards\Players\Human;
use Brightwood\Models\Cards\Restrictions\SuitRestriction;
use Brightwood\Models\Cards\Sets\Deck;
use Brightwood\Models\Cards\Sets\EightsDiscard;
use Brightwood\Models\Cards\Sets\Hand;
use Brightwood\Models\Cards\Sets\Pile;
use Brightwood\Serialization\Cards\Serializers\CardListSerializer;
use Brightwood\Serialization\Cards\Serializers\Players\BotSerializer;
use Brightwood\Serialization\Cards\Serializers\Players\HumanSerializer;
use Brightwood\Serialization\Cards\Serializers\Restrictions\SuitRestrictionSerializer;
use Brightwood\Serialization\Cards\SerializerSource;

class SerializationConfig extends SerializerSource
{
    public function __construct(
        TelegramUserRepositoryInterface $telegramUserRepository
    )
    {
        $botSerializer = new BotSerializer();
        $cardListSerializer = new CardListSerializer();

        return parent::__construct(
            [
                Bot::class => $botSerializer,
                Deck::class => $cardListSerializer,
                EightsDiscard::class => $cardListSerializer,
                FemaleBot::class => $botSerializer,
                Hand::class => $cardListSerializer,
                Human::class => new HumanSerializer($telegramUserRepository),
                Pile::class => $cardListSerializer,
                SuitRestriction::class => new SuitRestrictionSerializer(),
            ]
        );
    }
}
