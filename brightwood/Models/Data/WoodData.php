<?php

namespace Brightwood\Models\Data;

use Plasticode\Exceptions\InvalidOperationException;
use Webmozart\Assert\Assert;

/**
 * @property int $day Current day number.
 * @property int $hp Player's HP.
 * @property int $shoes The number of player's shoes.
 */
class WoodData extends StoryData
{
    public const MAX_HP = 6;
    public const MAX_SHOES = 2;

    public function __construct()
    {
        $this->day = 1;
        $this->hp = self::MAX_HP;
        $this->shoes = self::MAX_SHOES;
    }

    /**
     * Returns true if the player's HP is greater than 0.
     */
    public function isAlive() : bool
    {
        return $this->hp > 0;
    }

    /**
     * Returns true if the player's HP is <= 0.
     * 
     * Shortcut for !isAlive().
     */
    public function isDead() : bool
    {
        return !$this->isAlive();
    }

    /**
     * Returns true if the player has shoes.
     */
    public function hasShoes() : bool
    {
        return $this->shoes > 0;
    }

    /**
     * Increments the current day.
     */
    public function nextDay() : void
    {
        $this->day++;
    }

    /**
     * Removes one shoe.
     * 
     * Throws {@see InvalidOperationException} if the player has no shoes.
     * 
     * @throws InvalidOperationException
     */
    public function removeShoe() : void
    {
        if (!$this->hasShoes()) {
            throw new InvalidOperationException('No shows to remove.');
        }

        $this->shoes--;
    }

    /**
     * Hits the player for $amount HP down to 0.
     * 
     * @param int $amount Must be non-negative.
     * @throws \InvalidArgumentException
     */
    public function hit(int $amount) : void
    {
        Assert::natural($amount);

        $this->hp = max(
            $this->hp - $amount,
            0
        );
    }

    /**
     * Heals the player for $amount HP up to MAX_HP.
     * 
     * If the player is already dead, they can't be healed (nothing happens).
     * 
     * @param int $amount Must be non-negative int.
     * @throws \InvalidArgumentException
     */
    public function heal(int $amount) : void
    {
        Assert::natural($amount);

        if ($this->isDead()) {
            return;
        }

        $this->hp = min(
            $this->hp + $amount,
            self::MAX_HP
        );
    }
}
