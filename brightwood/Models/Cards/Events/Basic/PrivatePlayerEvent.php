<?php

namespace Brightwood\Models\Cards\Events\Basic;

use Brightwood\Models\Cards\Players\Player;

abstract class PrivatePlayerEvent extends PlayerEvent
{
    abstract public function privateChunk() : string;

    public function messageFor(?Player $player) : string
    {
        return $player && $player->isInspector()
            ? $this->privateMessage()
            : parent::messageFor($player);
    }

    private function privateMessage() : string
    {
        $sentence = $this
            ->chain
            ->toSentence(
                fn (PlayerEvent $e) => $e instanceof self
                    ? $e->privateChunk()
                    : $e->publicChunk()
            );

        return $this->player . ' ' . $sentence;
    }
}
