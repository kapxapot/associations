<?php

namespace Brightwood\Serialization\Cards\Serializers\Games;

use Brightwood\Models\Cards\Games\EightsGame;
use Brightwood\Models\Cards\Players\Player;
use Brightwood\Parsing\StoryParser;
use Brightwood\Serialization\Cards\Interfaces\RootDeserializerInterface;
use Plasticode\Util\Cases;

class EightsGameSerializer extends CardGameSerializer
{
    private StoryParser $parser;
    private Cases $cases;

    public function __construct(
        StoryParser $parser,
        Cases $cases
    )
    {
        $this->parser = $parser;
        $this->cases = $cases;
    }

    /**
     * @param EightsGame $obj
     */
    public function deserialize(
        RootDeserializerInterface $rootDeserializer,
        object $obj,
        array $data
    ): EightsGame
    {
        /** @var EightsGame */
        $obj = parent::deserialize($rootDeserializer, $obj, $data);

        return $obj
            ->withParser($this->parser)
            ->withCases($this->cases)
            ->withGift(
                $rootDeserializer->deserialize($data['gift'])
            )
            ->withCurrentPlayer(
                $rootDeserializer->resolvePlayer($data['current_player_id'] ?? null)
            )
            ->withMove($data['move'])
            ->withNoCardsInARow($data['no_cards_in_a_row'])
            ->withShowPlayersLine($data['show_players_line']);
    }
}
