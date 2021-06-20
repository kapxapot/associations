<?php

namespace App\EventHandlers\Word;

use App\Events\Word\WordMatureChangedEvent;
use App\Models\Association;
use App\Models\Word;
use App\Services\AssociationRecountService;
use App\Services\WordRecountService;

/**
 * If the word is mature, all its associations and dependent words must be mature.
 */
class WordMatureChangedHandler
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

    public function __invoke(WordMatureChangedEvent $event) : void
    {
        $word = $event->getWord();

        $word
            ->associations()
            ->apply(
                fn (Association $a) =>
                $this->associationRecountService->recountMature($a, $event)
            );

        $word
            ->dependents()
            ->apply(
                fn (Word $w) => $this->wordRecountService->recountMature($w, $event)
            );
    }
}
