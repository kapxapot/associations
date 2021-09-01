<?php

namespace App\EventHandlers\DictWord;

use App\Events\DictWord\DictWordUnlinkedEvent;
use App\Services\WordRecountService;

class DictWordUnlinkedHandler
{
    private WordRecountService $wordRecountService;

    public function __construct(WordRecountService $wordRecountService)
    {
        $this->wordRecountService = $wordRecountService;
    }

    public function __invoke(DictWordUnlinkedEvent $event) : void
    {
        $word = $event->getUnlinkedWord();

        $this->wordRecountService->recountScope($word, $event);
    }
}
