<?php

namespace App\EventHandlers\Definition;

use App\Events\Definition\DefinitionUnlinkedEvent;
use App\Services\WordRecountService;

class DefinitionUnlinkedHandler
{
    private WordRecountService $wordRecountService;

    public function __construct(WordRecountService $wordRecountService)
    {
        $this->wordRecountService = $wordRecountService;
    }

    public function __invoke(DefinitionUnlinkedEvent $event): void
    {
        $word = $event->getUnlinkedWord();

        $this->wordRecountService->recountApproved($word, $event);
    }
}
