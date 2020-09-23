<?php

namespace Brightwood\Models\Cards\Events\Basic;

use Brightwood\Models\Cards\Events\Interfaces\CardEventInterface;
use Brightwood\Models\Cards\Players\Player;

class PublicEvent implements CardEventInterface
{
    protected string $message;

    public function __construct(
        string $message
    )
    {
        $this->message = $message;
    }

    public function messageFor(?Player $player): string
    {
        return '<i>' . $this->message . '</i>';
    }
}
