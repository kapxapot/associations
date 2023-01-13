<?php

namespace Brightwood\Models\Cards\Actions\Eights;

use Brightwood\Models\Cards\Actions\SkipGiftAction;
use Brightwood\Models\Cards\Card;
use Brightwood\Models\Cards\Players\Player;
use Brightwood\Models\Cards\Rank;

class JackGiftAction extends SkipGiftAction
{
    public function __construct(
        ?Card $card = null,
        ?Player $sender = null
    )
    {
        parent::__construct($card, $sender, Rank::jack()->nameRu());
    }
}
