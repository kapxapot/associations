<?php

namespace Brightwood\Collections\Cards;

use Brightwood\Models\Cards\Events\Generic\PlayerEvent;
use Plasticode\Semantics\Sentence;

class PlayerEventCollection extends CardEventCollection
{
    protected string $class = PlayerEvent::class;

    public function toSentence(
        callable $extractor,
        ?string $commaDelimiter = null,
        ?string $andDelimiter = null
    ): string
    {
        $chunks = $this->map($extractor);

        return Sentence::homogeneousJoin($chunks, $commaDelimiter, $andDelimiter);
    }
}
