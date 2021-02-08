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
use App\Services\DefinitionService;
use App\Services\DictionaryService;
use App\Services\WordRecountService;
use Plasticode\Mapping\Providers\Generic\MappingProvider;
use Psr\Container\ContainerInterface;

class EventProvider extends MappingProvider
{
    public function getEventHandlers(ContainerInterface $container): array
    {
        return [
            new AssociationApprovedChangedHandler(
                $container->get(WordRecountService::class)
            ),

            new AssociationFeedbackCreatedHandler(
                $container->get(AssociationRecountService::class)
            ),

            new AssociationOutOfDateHandler(
                $container->get(AssociationRecountService::class)
            ),

            new DictWordLinkedHandler(
                $container->get(WordRecountService::class)
            ),

            new DictWordUnlinkedHandler(
                $container->get(WordRecountService::class)
            ),

            new TurnCreatedHandler(
                $container->get(AssociationRecountService::class)
            ),

            new WordCreatedHandler(
                $container->get(DefinitionService::class),
                $container->get(DictionaryService::class)
            ),

            new WordFeedbackCreatedHandler(
                $container->get(WordRecountService::class)
            ),

            new WordMatureChangedHandler(
                $container->get(AssociationRecountService::class)
            ),

            new WordOutOfDateHandler(
                $container->get(WordRecountService::class)
            ),

            new WordUpdatedHandler(
                $container->get(DictionaryService::class)
            ),
        ];
    }
}
