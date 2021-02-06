<?php

namespace App\Events\DictWord;

use App\Models\Interfaces\DictWordInterface;
use Plasticode\Events\EntityEvent;
use Plasticode\Events\Event;

abstract class DictWordEvent extends EntityEvent
{
    protected DictWordInterface $dictWord;

    public function __construct(DictWordInterface $dictWord, ?Event $parent = null)
    {
        parent::__construct($parent);

        $this->dictWord = $dictWord;
    }

    public function getDictWord(): DictWordInterface
    {
        return $this->dictWord;
    }

    public function getEntity(): DictWordInterface
    {
        return $this->getDictWord();
    }
}
