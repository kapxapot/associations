<?php

namespace App\Models\DTO;

use App\Models\Association;
use App\Models\Word;

class MetaTurn
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
