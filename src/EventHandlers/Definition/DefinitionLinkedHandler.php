<?php

namespace App\EventHandlers\Definition;

use App\Events\Definition\DefinitionLinkedEvent;
use App\Services\WordRecountService;
use Webmozart\Assert\Assert;

class DefinitionLinkedHandler
{
    private WordRecountService $wordRecountService;

    public function __construct(WordRecountService $wordRecountService)
    {
        $this->wordRecountService = $wordRecountService;
    }

    public function __invoke(DefinitionLinkedEvent $event): void
    {
        $word = $event->getDefinition()->word();

        Assert::notNull($word);

        $this->wordRecountService->recountScope($word, $event);
    }
}
