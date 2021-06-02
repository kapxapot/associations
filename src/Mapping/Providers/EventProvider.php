<?php

namespace App\Mapping\Providers;

use App\EventHandlers\Association\AssociationApprovedChangedHandler;
use App\EventHandlers\Association\AssociationCreatedHandler;
use App\EventHandlers\Association\AssociationOutOfDateHandler;
use App\EventHandlers\Definition\DefinitionLinkedHandler;
use App\EventHandlers\Definition\DefinitionUnlinkedHandler;
use App\EventHandlers\DictWord\DictWordLinkedHandler;
use App\EventHandlers\DictWord\DictWordUnlinkedHandler;
use App\EventHandlers\Feedback\AssociationFeedbackCreatedHandler;
use App\EventHandlers\Feedback\WordFeedbackCreatedHandler;
use App\EventHandlers\Override\AssociationOverrideCreatedHandler;
use App\EventHandlers\Override\WordOverrideCreatedHandler;
use App\EventHandlers\Turn\TurnCreatedHandler;
use App\EventHandlers\Word\WordApprovedChangedHandler;
use App\EventHandlers\Word\WordCorrectedHandler;
use App\EventHandlers\Word\WordCreatedHandler;
use App\EventHandlers\Word\WordDisabledChangedHandler;
use App\EventHandlers\Word\WordMatureChangedHandler;
use App\EventHandlers\Word\WordOutOfDateHandler;
use App\EventHandlers\Word\WordRelationsChangedHandler;
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

            new AssociationCreatedHandler(
                $container->get(AssociationRecountService::class)
            ),

            new AssociationFeedbackCreatedHandler(
                $container->get(AssociationRecountService::class)
            ),

            new AssociationOverrideCreatedHandler(
                $container->get(AssociationRecountService::class)
            ),

            new AssociationOutOfDateHandler(
                $container->get(AssociationRecountService::class)
            ),

            new DefinitionLinkedHandler(
                $container->get(WordRecountService::class)
            ),

            new DefinitionUnlinkedHandler(
                $container->get(WordRecountService::class)
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

            new WordApprovedChangedHandler(
                $container->get(AssociationRecountService::class)
            ),

            new WordCorrectedHandler(
                $container->get(DefinitionService::class),
                $container->get(DictionaryService::class)
            ),

            new WordCreatedHandler(
                $container->get(DefinitionService::class),
                $container->get(DictionaryService::class)
            ),

            new WordDisabledChangedHandler(
                $container->get(AssociationRecountService::class)
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

            new WordOverrideCreatedHandler(
                $container->get(WordRecountService::class)
            ),

            new WordRelationsChangedHandler(
                $container->get(AssociationRecountService::class),
                $container->get(WordRecountService::class)
            ),
        ];
    }
}
