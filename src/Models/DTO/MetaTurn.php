<?php

namespace App\Models\DTO;

use App\Models\Association;
use App\Models\Word;

class MetaTurn
{
    private ?Association $association;
    private ?Word $word;

    public function __construct(
        ?Association $association,
        ?Word $word
    )
    {
        $this->association = $association;
        $this->word = $word;
    }

    public function association(): ?Association
    {
        return $this->association;
    }

    public function word(): ?Word
    {
        return $this->word;
    }
}
