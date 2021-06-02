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
use Plasticode\Collections\Generic\StringCollection;
use Plasticode\Mapping\Providers\Generic\MappingProvider;
use Psr\Container\ContainerInterface;

class EventProvider extends MappingProvider
{
    public function getEventHandlers(ContainerInterface $container): array
    {
        $classes = StringCollection::collect(
            AssociationApprovedChangedHandler::class,
            AssociationCreatedHandler::class,
            AssociationFeedbackCreatedHandler::class,
            AssociationOverrideCreatedHandler::class,
            AssociationOutOfDateHandler::class,
            DefinitionLinkedHandler::class,
            DefinitionUnlinkedHandler::class,
            DictWordLinkedHandler::class,
            DictWordUnlinkedHandler::class,
            TurnCreatedHandler::class,
            WordApprovedChangedHandler::class,
            WordCorrectedHandler::class,
            WordCreatedHandler::class,
            WordDisabledChangedHandler::class,
            WordFeedbackCreatedHandler::class,
            WordMatureChangedHandler::class,
            WordOutOfDateHandler::class,
            WordOverrideCreatedHandler::class,
            WordRelationsChangedHandler::class,
        );

        $handlers = $classes->map(
            fn (string $c) => $container->get($c)
        );

        return $handlers->toArray();
    }
}
