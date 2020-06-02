<?php

namespace App\EventHandlers\DictWord;

use App\Events\DictWord\DictWordLinkedEvent;
use App\Services\WordRecountService;
use Webmozart\Assert\Assert;

class DictWordLinkedHandler
{
    private WordRecountService $wordRecountService;

    public function __construct(WordRecountService $wordRecountService)
    {
        $this->wordRecountService = $wordRecountService;
    }

    public function __invoke(DictWordLinkedEvent $event) : void
    {
        $word = $event->getDictWord()->getLinkedWord();

        Assert::notNull($word);

        $this->wordRecountService->recountApproved($word, $event);
    }
}
