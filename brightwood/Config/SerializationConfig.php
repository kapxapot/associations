<?php

namespace Brightwood\Config;

use App\Repositories\Interfaces\TelegramUserRepositoryInterface;
use Brightwood\Models\Cards\Actions\Eights\EightGiftAction;
use Brightwood\Models\Cards\Actions\Eights\JackGiftAction;
use Brightwood\Models\Cards\Actions\Eights\SevenGiftAction;
use Brightwood\Models\Cards\Actions\Eights\SixGiftAction;
use Brightwood\Models\Cards\Actions\SkipGiftAction;
use Brightwood\Models\Cards\Games\EightsGame;
use Brightwood\Models\Cards\Players\Bot;
use Brightwood\Models\Cards\Players\FemaleBot;
use Brightwood\Models\Cards\Players\Human;
use Brightwood\Models\Cards\Restrictions\SuitRestriction;
use Brightwood\Models\Cards\Sets\Deck;
use Brightwood\Models\Cards\Sets\EightsDiscard;
use Brightwood\Models\Cards\Sets\Hand;
use Brightwood\Models\Cards\Sets\Pile;
use Brightwood\Models\Data\EightsData;
use Brightwood\Parsing\StoryParser;
use Brightwood\Serialization\Cards\Serializers\Actions\EightGiftActionSerializer;
use Brightwood\Serialization\Cards\Serializers\Actions\GiftActionSerializer;
use Brightwood\Serialization\Cards\Serializers\Actions\SkipGiftActionSerializer;
use Brightwood\Serialization\Cards\Serializers\CardListSerializer;
use Brightwood\Serialization\Cards\Serializers\Data\EightsDataSerializer;
use Brightwood\Serialization\Cards\Serializers\Games\EightsGameSerializer;
use Brightwood\Serialization\Cards\Serializers\Players\BotSerializer;
use Brightwood\Serialization\Cards\Serializers\Players\HumanSerializer;
use Brightwood\Serialization\Cards\Serializers\Restrictions\SuitRestrictionSerializer;
use Brightwood\Serialization\Cards\SerializerSource;
use Plasticode\Util\Cases;

class SerializationConfig extends SerializerSource
{
    public function __construct(
        TelegramUserRepositoryInterface $telegramUserRepository,
        StoryParser $parser,
        Cases $cases
    )
    {
        $botSerializer = new BotSerializer();
        $cardListSerializer = new CardListSerializer();
        $giftActionSerializer = new GiftActionSerializer();
        $skipGiftActionSerializer = new SkipGiftActionSerializer();

        return parent::__construct([
            Bot::class => $botSerializer,
            Deck::class => $cardListSerializer,
            EightGiftAction::class => new EightGiftActionSerializer(),
            EightsData::class => new EightsDataSerializer(),
            EightsDiscard::class => $cardListSerializer,
            EightsGame::class => new EightsGameSerializer($parser, $cases),
            FemaleBot::class => $botSerializer,
            Hand::class => $cardListSerializer,
            Human::class => new HumanSerializer($telegramUserRepository),
            JackGiftAction::class => $skipGiftActionSerializer,
            Pile::class => $cardListSerializer,
            SevenGiftAction::class => $giftActionSerializer,
            SixGiftAction::class => $giftActionSerializer,
            SkipGiftAction::class => $skipGiftActionSerializer,
            SuitRestriction::class => new SuitRestrictionSerializer(),
        ]);
    }
}
