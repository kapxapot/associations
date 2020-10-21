<?php

namespace Brightwood\Tests\Models\Cards\Actions;

use Brightwood\Models\Cards\Actions\Eights\JackGiftAction;
use Brightwood\Models\Cards\Rank;
use Brightwood\Models\Cards\Suit;
use Brightwood\Models\Cards\SuitedCard;
use Brightwood\Tests\SerializationTestCase;

final class JackGiftActionTest extends SerializationTestCase
{
    private string $reason = 'some reason';
    private string $json = '{"type":"Brightwood\\\\Models\\\\Cards\\\\Actions\\\\Eights\\\\JackGiftAction","data":{"card":"\u2660J","sender_id":"59f628bbf4cb3c3b44ae","reason":"some reason"}}';

    public function testSerialize() : void
    {
        $gift = new JackGiftAction(
            new SuitedCard(Suit::spades(), Rank::jack()),
            $this->player,
            $this->reason
        );

        $json = json_encode($gift);

        $this->assertEquals($this->json, $json);
    }

    public function testDeserialize() : void
    {
        $jsonData = json_decode($this->json, true);
        $gift = $this->deserializer->deserialize($jsonData);

        $this->assertInstanceOf(JackGiftAction::class, $gift);

        $this->assertTrue(
            $gift->sender()->equals($this->player)
        );

        $this->assertEquals(
            $this->reason,
            $gift->reason()
        );

        $this->assertTrue(
            $gift->card()->equals(
                new SuitedCard(Suit::spades(), Rank::jack())
            )
        );
    }
}
