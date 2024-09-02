<?php

namespace Brightwood\Tests;

use Brightwood\Models\Cards\Players\Bot;
use Brightwood\Testing\Factories\RootDeserializerTestFactory;
use PHPUnit\Framework\TestCase;

final class RootDeserializerTest extends TestCase
{
    public function testAddPlayersDoesntAddDuplicates(): void
    {
        $deserializer = RootDeserializerTestFactory::make();

        $this->assertEmpty($deserializer->players());

        $player1 = (new Bot())->withId('abcd');
        $player2 = (new Bot())->withId('abcd');

        $deserializer->addPlayers($player1);
        $this->assertCount(1, $deserializer->players());

        $deserializer->addPlayers($player2);
        $this->assertCount(1, $deserializer->players());
    }
}
