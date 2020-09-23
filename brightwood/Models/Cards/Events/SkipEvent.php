<?php

namespace Brightwood\Models\Cards\Events;

use Brightwood\Models\Cards\Events\Basic\PlayerEvent;
use Brightwood\Models\Cards\Players\Player;

class SkipEvent extends PlayerEvent
{
    private ?string $reason = null;

    public function __construct(
        Player $player,
        ?string $reason = null
    )
    {
        parent::__construct($player);

        $this->reason = $reason;
    }

    public function publicChunk() : string
    {
        return $this->withReason('пропускает ход');
    }

    public function personalChunk() : string
    {
        return $this->withReason('пропускаете ход');
    }

    private function withReason(string $text) : string
    {
        return $this->reason
            ? $text . ' (' . $this->reason . ')'
            : $text;
    }
}
