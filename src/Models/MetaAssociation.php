<?php

namespace App\Models;

use Plasticode\Models\Basic\Model;

/**
 * DTO for {@see Word} origin chain.
 */
class MetaAssociation extends Model
{
    private Association $association;
    private Word $toWord;

    public function __construct(
        Word $toWord,
        Association $association
    )
    {
        parent::__construct();

        $this->toWord = $toWord;
        $this->association = $association;
    }

    public function association() : Association
    {
        return $this->association;
    }

    public function toWord() : Word
    {
        return $this->toWord;
    }

    public function user() : User
    {
        return $this->association->creator();
    }

    public function fromWord() : Word
    {
        return $this->association->otherWord($this->toWord);
    }
}
