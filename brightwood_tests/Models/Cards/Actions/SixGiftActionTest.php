<?php

namespace Brightwood\Tests\Models\Cards\Actions;

use Brightwood\Models\Cards\Actions\Eights\SixGiftAction;
use Brightwood\Models\Cards\Rank;
use Brightwood\Models\Cards\Suit;
use Brightwood\Models\Cards\SuitedCard;
use Brightwood\Tests\SerializationTestCase;

final class SixGiftActionTest extends SerializationTestCase
{
    private string $json = '{"type":"Brightwood\\\\Models\\\\Cards\\\\Actions\\\\Eights\\\\SixGiftAction","data":{"card":"\u26606","sender_id":"59f628bbf4cb3c3b44ae"}}';

    public function testSerialize() : void
    {
        $gift = new SixGiftAction(
            new SuitedCard(Suit::spades(), Rank::six()),
            $this->player
        );

        $json = json_encode($gift);

        $this->assertEquals($this->json, $json);
    }

    public function testDeserialize() : void
    {
        $jsonData = json_decode($this->json, true);
        $gift = $this->deserializer->deserialize($jsonData);

        $this->assertInstanceOf(SixGiftAction::class, $gift);

        $this->assertTrue(
            $gift->sender()->equals($this->player)
        );

        $this->assertTrue(
            $gift->card()->equals(
                new SuitedCard(Suit::spades(), Rank::six())
            )
        );
    }
}
