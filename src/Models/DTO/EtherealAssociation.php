<?php

namespace App\Models\DTO;

use App\Models\Association;
use App\Models\Interfaces\AssociationInterface;
use App\Models\Word;

class EtherealAssociation implements AssociationInterface
{
    private Word $firstWord;
    private Word $secondWord;

    public function __construct(
        Word $firstWord,
        Word $secondWord
    )
    {
        $this->firstWord = $firstWord;
        $this->secondWord = $secondWord;
    }

    public function getFirstWord(): Word
    {
        return $this->firstWord;
    }

    public function getSecondWord(): Word
    {
        return $this->secondWord;
    }

    public function isReal(): bool
    {
        return false;
    }

    public function toReal(): ?Association
    {
        return null;
    }

    public function key(): string
    {
        return $this->firstWord->getId() . ':' . $this->secondWord->getId();
    }
}
