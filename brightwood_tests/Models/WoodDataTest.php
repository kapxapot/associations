<?php

namespace Brightwood\Tests\Models;

use Brightwood\Models\Data\WoodData;
use PHPUnit\Framework\TestCase;
use Plasticode\Exceptions\InvalidOperationException;

final class WoodDataTest extends TestCase
{
    public function testDays(): void
    {
        $data = new WoodData();
        $this->assertEquals(1, $data->day);
        $data->nextDay();
        $this->assertEquals(2, $data->day);
    }

    public function testShoes(): void
    {
        $data = new WoodData();
        $max = WoodData::MAX_SHOES;

        $this->assertEquals($max, $data->shoes);

        for ($i = 1; $i <= $max; $i++) {
            $this->assertTrue($data->hasShoes());
            $data->removeShoe();
            $this->assertEquals($max - $i, $data->shoes);
        }

        $this->assertEquals(0, $data->shoes);
        $this->assertFalse($data->hasShoes());

        $this->expectException(InvalidOperationException::class);

        $data->removeShoe();
    }

    public function testHitting(): void
    {
        $data = new WoodData();
        $max = WoodData::MAX_HP;

        $this->assertEquals($max, $data->hp);
        $this->assertTrue($data->isAlive());
        $this->assertFalse($data->isDead());

        $data->hit($max - 1);

        $this->assertEquals(1, $data->hp);
        $this->assertTrue($data->isAlive());

        // hitting to death
        $data->hit(1);

        $this->assertEquals(0, $data->hp);
        $this->assertFalse($data->isAlive());
        $this->assertTrue($data->isDead());

        // more hitting does nothing
        $data->hit(1);

        $this->assertEquals(0, $data->hp);
        $this->assertTrue($data->isDead());
    }

    public function testHittingForNegativeAmount(): void
    {
        $data = new WoodData();

        $this->expectException(\InvalidArgumentException::class);

        $data->hit(-1);
    }

    public function testHealing(): void
    {
        $data = new WoodData();
        $max = WoodData::MAX_HP;

        $data->hit(1);

        $this->assertEquals($max - 1, $data->hp);

        // healing up to max hp
        $data->heal(10);

        $this->assertEquals($max, $data->hp);
        $this->assertTrue($data->isAlive());

        // more healing does nothing
        $data->heal(1);

        $this->assertEquals($max, $data->hp);
        $this->assertTrue($data->isAlive());
    }

    public function testHealingForNegativeAmount(): void
    {
        $data = new WoodData();

        $this->expectException(\InvalidArgumentException::class);

        $data->heal(-1);
    }

    public function testHealingDead(): void
    {
        $data = new WoodData();

        $max = WoodData::MAX_HP;

        $data->hit($max);

        $this->assertEquals(0, $data->hp);
        $this->assertTrue($data->isDead());

        // healing dead does nothing
        $data->heal(1);

        $this->assertEquals(0, $data->hp);
        $this->assertTrue($data->isDead());
    }
}
