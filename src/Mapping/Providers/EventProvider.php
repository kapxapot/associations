<?php

namespace App\Mapping\Providers;

use App\EventHandlers\Association\AssociationApprovedChangedHandler;
use App\EventHandlers\Association\AssociationOutOfDateHandler;
use App\EventHandlers\DictWord\DictWordLinkedHandler;
use App\EventHandlers\DictWord\DictWordUnlinkedHandler;
use App\EventHandlers\Feedback\AssociationFeedbackCreatedHandler;
use App\EventHandlers\Feedback\WordFeedbackCreatedHandler;
use App\EventHandlers\Turn\TurnCreatedHandler;
use App\EventHandlers\Word\WordCreatedHandler;
use App\EventHandlers\Word\WordMatureChangedHandler;
use App\EventHandlers\Word\WordOutOfDateHandler;
use App\EventHandlers\Word\WordUpdatedHandler;
use App\Services\AssociationRecountService;
use App\Services\DictionaryService;
use App\Services\WordRecountService;
use Plasticode\Mapping\Providers\Generic\MappingProvider;
use Psr\Container\ContainerInterface;

class EventProvider extends MappingProvider
{
    public function getEventHandlers(): array
    {
        return [
            fn (ContainerInterface $c) =>
                new AssociationApprovedChangedHandler(
                    $c->get(WordRecountService::class)
                ),

            fn (ContainerInterface $c) =>
                new AssociationFeedbackCreatedHandler(
                    $c->get(AssociationRecountService::class)
                ),

            fn (ContainerInterface $c) =>
                new AssociationOutOfDateHandler(
                    $c->get(AssociationRecountService::class)
                ),

            fn (ContainerInterface $c) =>
                new DictWordLinkedHandler(
                    $c->get(WordRecountService::class)
                ),

            fn (ContainerInterface $c) =>
                new DictWordUnlinkedHandler(
                    $c->get(WordRecountService::class)
                ),

            fn (ContainerInterface $c) =>
                new TurnCreatedHandler(
                    $c->get(AssociationRecountService::class)
                ),

            fn (ContainerInterface $c) =>
                new WordCreatedHandler(
                    $c->get(DictionaryService::class)
                ),

            fn (ContainerInterface $c) =>
                new WordFeedbackCreatedHandler(
                    $c->get(WordRecountService::class)
                ),

            fn (ContainerInterface $c) =>
                new WordMatureChangedHandler(
                    $c->get(AssociationRecountService::class)
                ),

            fn (ContainerInterface $c) =>
                new WordOutOfDateHandler(
                    $c->get(WordRecountService::class)
                ),

            fn (ContainerInterface $c) =>
                new WordUpdatedHandler(
                    $c->get(DictionaryService::class)
                ),
        ];
    }
}
