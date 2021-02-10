<?php

namespace App\EventHandlers\Definition;

use App\Events\Definition\DefinitionUpdatedEvent;
use App\Services\WordRecountService;
use Webmozart\Assert\Assert;

class DefinitionUpdatedHandler
{
    private WordRecountService $wordRecountService;

    public function __construct(WordRecountService $wordRecountService)
    {
        $this->wordRecountService = $wordRecountService;
    }

    public function __invoke(DefinitionUpdatedEvent $event): void
    {
        $word = $event->getDefinition()->word();

        Assert::notNull($word);

        $this->wordRecountService->recountApproved($word, $event);
    }
}
