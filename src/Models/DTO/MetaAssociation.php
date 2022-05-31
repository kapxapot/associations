<?php

namespace App\Models\DTO;

use App\Models\Association;
use App\Models\User;
use App\Models\Word;

/**
 * DTO for {@see Word} origin chain.
 */
class MetaAssociation
{
    private Association $association;
    private Word $toWord;

    public function __construct(
        Association $association,
        Word $toWord
    )
    {
        $this->association = $association;
        $this->toWord = $toWord;
    }

    public function association(): Association
    {
        return $this->association;
    }

    public function toWord(): Word
    {
        return $this->toWord;
    }

    public function user(): User
    {
        return $this->association->creator();
    }

    public function fromWord(): Word
    {
        return $this->association->otherWord($this->toWord);
    }
}
