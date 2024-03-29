<?php

namespace App\Collections;

use App\Semantics\PartOfSpeech;
use Plasticode\Collections\Generic\TypedCollection;

class PartOfSpeechCollection extends TypedCollection
{
    protected string $class = PartOfSpeech::class;

    public function get(?string $name): ?PartOfSpeech
    {
        return $this->first(
            fn (PartOfSpeech $p) => $p->name() === $name
        );
    }

    public function distinct(): self
    {
        return $this->distinctBy(
            fn (PartOfSpeech $p) => $p->name()
        );
    }

    public function isAnyGood(): bool
    {
        return $this->any(
            fn (PartOfSpeech $p) => $p->isGood()
        );
    }

    public function bestQuality(): ?int
    {
        return $this
            ->numerize(
                fn (PartOfSpeech $p) => $p->quality()
            )
            ->min();
    }
}
