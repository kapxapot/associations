<?php

namespace Brightwood\Models\Cards\Players;

use Plasticode\Collections\Basic\Collection;
use Plasticode\Util\Cases;

class Bot extends Player
{
    protected string $name;
    protected string $icon;
    protected int $gender;

    public function __construct(string $name, ?int $gender = null)
    {
        parent::__construct();

        $this->name = $name;

        $this->icon = Collection::make(
            ['🤖', '👽', '🐵', '🐶', '🐱', '🦊', '🐭', '🐹', '🐰', '🐻', '🐷', '🐯', '🐺', '🦝', '🐸', '🦁', '🐮', '🐗', '🐨', '🐼']
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
        return $this->icon . ' ' . $this->name;
    }

    // GenderedInterface

    public function gender() : int
    {
        return $this->gender;
    }
}
