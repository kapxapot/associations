<?php

namespace Brightwood\Models\Data;

use Plasticode\Exceptions\InvalidOperationException;
use Webmozart\Assert\Assert;

/**
 * @property int $day Current day number.
 * @property int $hp Player's HP.
 * @property int $shoes The number of player's shoes.
 * @property int $wandered Times the player wandered in search of the exit.
 * @property int $wanderedToday Times the player wandered this day.
 */
class WoodData extends StoryData
{
    public const MAX_HP = 6;
    public const MAX_SHOES = 2;
    public const WANDERED_ENOUGH = 5;
    public const MAX_WANDERS_PER_DAY = 3;

    protected function init() : void
    {
        $this->day = 1;
        $this->hp = self::MAX_HP;
        $this->shoes = self::MAX_SHOES;
        $this->wandered = 0;
        $this->wanderedToday = 0;
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
     * Increments the current day and decrements hp.
     */
    public function nextDay() : self
    {
        $this->day++;
        $this->wanderedToday = 0;

        return $this->hit(1);
    }

    /**
     * Removes one shoe.
     * 
     * Throws {@see InvalidOperationException} if the player has no shoes.
     * 
     * @throws InvalidOperationException
     */
    public function removeShoe() : self
    {
        if (!$this->hasShoes()) {
            throw new InvalidOperationException('No shows to remove.');
        }

        $this->shoes--;

        return $this;
    }

    /**
     * Brutally beats the player to death.
     */
    public function kill() : self
    {
        $this->hp = 0;

        return $this;
    }

    /**
     * Hits the player for $amount HP down to 0.
     * 
     * @param int $amount Must be non-negative.
     * @throws \InvalidArgumentException
     */
    public function hit(int $amount) : self
    {
        Assert::natural($amount);

        $this->hp = max(
            $this->hp - $amount,
            0
        );

        return $this;
    }

    /**
     * Heals the player for $amount HP up to MAX_HP.
     * 
     * If the player is already dead, they can't be healed (nothing happens).
     * 
     * @param int $amount Must be non-negative int.
     * @throws \InvalidArgumentException
     */
    public function heal(int $amount) : self
    {
        Assert::natural($amount);

        if ($this->isDead()) {
            return $this;
        }

        $this->hp = min(
            $this->hp + $amount,
            self::MAX_HP
        );

        return $this;
    }

    /**
     * Wander in the woods.
     */
    public function wander() : self
    {
        $this->wandered++;
        $this->wanderedToday;

        return $this;
    }

    /**
     * Has the player wandered enough to find the exit.
     */
    public function hasWanderedEnough() : bool
    {
        return $this->wandered >= self::WANDERED_ENOUGH;
    }
}
