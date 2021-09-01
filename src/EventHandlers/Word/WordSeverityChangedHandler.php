<?php

namespace App\EventHandlers\Word;

use App\Events\Word\WordSeverityChangedEvent;
use App\Models\Association;
use App\Models\Word;
use App\Services\AssociationRecountService;
use App\Services\WordRecountService;

/**
 * If the word is not neutral, all its associations and dependent words must have
 * the same (or higher) severity.
 */
class WordSeverityChangedHandler
{
    private AssociationRecountService $associationRecountService;
    private WordRecountService $wordRecountService;

    public function __construct(
        AssociationRecountService $associationRecountService,
        WordRecountService $wordRecountService
    )
    {
        $this->associationRecountService = $associationRecountService;
        $this->wordRecountService = $wordRecountService;
    }

    public function __invoke(WordSeverityChangedEvent $event) : void
    {
        $word = $event->getWord();

        $word
            ->associations()
            ->apply(
                fn (Association $a) =>
                    $this->associationRecountService->recountSeverity($a, $event)
            );

        $word
            ->dependents()
            ->apply(
                fn (Word $w) => $this->wordRecountService->recountSeverity($w, $event)
            );
    }
}
