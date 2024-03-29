<?php

namespace Brightwood\Models\Cards\Actions;

use Brightwood\Collections\Cards\CardEventCollection;
use Brightwood\Models\Cards\Actions\Interfaces\ApplicableActionInterface;
use Brightwood\Models\Cards\Actions\Interfaces\SkipActionInterface;
use Brightwood\Models\Cards\Card;
use Brightwood\Models\Cards\Events\Generic\PublicEvent;
use Brightwood\Models\Cards\Events\SkipEvent;
use Brightwood\Models\Cards\Games\CardGame;
use Brightwood\Models\Cards\Players\Player;

class SkipGiftAction extends GiftAction implements ApplicableActionInterface, SkipActionInterface
{
    private ?string $reason;

    public function __construct(
        ?Card $card = null,
        ?Player $sender = null,
        ?string $reason = null
    )
    {
        parent::__construct($card, $sender);

        $this->withReason($reason);
    }

    public function reason(): ?string
    {
        return $this->reason;
    }

    /**
     * @return $this
     */
    public function withReason(?string $reason): self
    {
        $this->reason = $reason;

        return $this;
    }

    public function announcementEvents(): CardEventCollection
    {
        return CardEventCollection::empty();
    }

    public function applyTo(CardGame $game, Player $player): CardEventCollection
    {
        return CardEventCollection::collect(
            new SkipEvent($player, $this->reason)
        );
    }

    // SerializableInterface

    /**
     * @param array[] $data
     */
    public function serialize(array ...$data): array
    {
        return parent::serialize(
            ['reason' => $this->reason]
        );
    }
}
