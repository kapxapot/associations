<?php

namespace Brightwood\Models\Cards\Players;

use Plasticode\Collections\Basic\Collection;
use Plasticode\Util\Cases;

class Bot extends Player
{
    protected string $name;
    protected int $gender;

    public function __construct(string $name, ?int $gender = null)
    {
        parent::__construct();

        $this->name = $name;

        $this->icon = Collection::collect(
            '🤖', '👽', '🐵', '🐶', '🐱', '🦊', '🐭', '🐹', '🐰', '🐻', '🐷', '🐯', '🐺', '🐸', '🦁', '🐮', '🐨', '🐼'
        )->random();

        $this->gender = $gender ?? Cases::MAS;
    }

    public function isBot() : bool
    {
        return true;
    }

    // NamedInterface

    public function name() : string
    {
        return $this->name;
    }

    // GenderedInterface

    public function gender() : int
    {
        return $this->gender;
    }

    // JsonSerializable

    public function jsonSerialize()
    {
        $data = parent::jsonSerialize();

        $data['name'] = $this->name;
        $data['gender'] = $this->gender;

        return $data;
    }
}
