<?php

namespace Brightwood\Models\Cards\Players;

class Bot extends Player
{
    protected string $name;

    public function __construct(string $name)
    {
        parent::__construct();

        $this->name = $name;
    }

    public function name() : string
    {
        return $this->name;
    }

    public function isBot() : bool
    {
        return true;
    }
}
