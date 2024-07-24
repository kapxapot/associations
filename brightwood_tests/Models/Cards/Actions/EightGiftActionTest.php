<?php

namespace Brightwood\Tests\Models\Cards\Actions;

use Brightwood\Models\Cards\Actions\Eights\EightGiftAction;
use Brightwood\Models\Cards\Rank;
use Brightwood\Models\Cards\Suit;
use Brightwood\Models\Cards\SuitedCard;
use Brightwood\Tests\SerializationTestCase;

final class EightGiftActionTest extends SerializationTestCase
{
    private string $json = '{"type":"Brightwood\\\\Models\\\\Cards\\\\Actions\\\\Eights\\\\EightGiftAction","data":{"card":"\u26608","sender_id":"59f628bbf4cb3c3b44ae","suit":"\u2665"}}';

    public function testSerialize(): void
    {
        $gift = new EightGiftAction(
            new SuitedCard(Suit::spades(), Rank::eight()),
            Suit::hearts(),
            $this->player
        );

        $json = json_encode($gift);

        $this->assertEquals($this->json, $json);
    }

    public function testDeserialize(): void
    {
        $jsonData = json_decode($this->json, true);
        $gift = $this->deserializer->deserialize($jsonData);

        $this->assertInstanceOf(EightGiftAction::class, $gift);

        $this->assertTrue(
            $gift->sender()->equals($this->player)
        );

        $this->assertTrue(
            $gift->suit()->equals(Suit::hearts())
        );

        $this->assertTrue(
            $gift->card()->equals(
                new SuitedCard(Suit::spades(), Rank::eight())
            )
        );
    }
}
