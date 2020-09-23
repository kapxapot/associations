<?php

namespace Brightwood\Collections\Cards;

use App\Semantics\Sentence;
use Brightwood\Models\Cards\Events\Basic\PlayerEvent;

class PlayerEventCollection extends CardEventCollection
{
    protected string $class = PlayerEvent::class;

    public function toSentence(
        callable $extractor,
        ?string $commaDelimiter = null,
        ?string $andDelimiter = null
    ) : string
    {
        $chunks = $this->map($extractor);

        return Sentence::homogeneousJoin($chunks, $commaDelimiter, $andDelimiter);
    }
}
