<?php

namespace App\Models\DTO;

use App\Models\Association;
use App\Models\Interfaces\TurnInterface;
use App\Models\Word;

class PseudoTurn implements TurnInterface
{
    private ?Association $association;
    private ?Word $word;
    private ?Word $prevWord;

    public function __construct(
        ?Association $association,
        ?Word $word,
        ?Word $prevWord
    )
    {
        $this->association = $association;
        $this->word = $word;
        $this->prevWord = $prevWord;
    }

    public static function empty(?Word $prevWord = null): self
    {
        return new self(null, null, $prevWord);
    }

    public static function new(?Word $word): self
    {
        return new self(null, $word, null);
    }

    public function association(): ?Association
    {
        return $this->association;
    }

    public function word(): ?Word
    {
        return $this->word;
    }

    public function prevWord(): ?Word
    {
        return $this->prevWord;
    }
}
