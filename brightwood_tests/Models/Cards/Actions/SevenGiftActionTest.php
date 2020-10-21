<?php

namespace Brightwood\Tests\Models\Cards\Actions;

use Brightwood\Models\Cards\Actions\Eights\SevenGiftAction;
use Brightwood\Models\Cards\Rank;
use Brightwood\Models\Cards\Suit;
use Brightwood\Models\Cards\SuitedCard;
use Brightwood\Tests\SerializationTestCase;

final class SevenGiftActionTest extends SerializationTestCase
{
    private string $json = '{"type":"Brightwood\\\\Models\\\\Cards\\\\Actions\\\\Eights\\\\SevenGiftAction","data":{"card":"\u26607","sender_id":"59f628bbf4cb3c3b44ae"}}';

    public function testSerialize() : void
    {
        $gift = new SevenGiftAction(
            new SuitedCard(Suit::spades(), Rank::seven()),
            $this->player
        );

        $json = json_encode($gift);

        $this->assertEquals($this->json, $json);
    }

    public function testDeserialize() : void
    {
        $jsonData = json_decode($this->json, true);
        $gift = $this->deserializer->deserialize($jsonData);

        $this->assertInstanceOf(SevenGiftAction::class, $gift);

        $this->assertTrue(
            $gift->sender()->equals($this->player)
        );

        $this->assertTrue(
            $gift->card()->equals(
                new SuitedCard(Suit::spades(), Rank::seven())
            )
        );
    }
}
