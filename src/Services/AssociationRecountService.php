<?php

namespace App\Services;

use App\Events\Association\AssociationScopeChangedEvent;
use App\Events\Association\AssociationSeverityChangedEvent;
use App\Models\Association;
use App\Models\Word;
use App\Repositories\Interfaces\AssociationRepositoryInterface;
use App\Specifications\AssociationSpecification;
use Plasticode\Events\Event;
use Plasticode\Events\EventDispatcher;
use Plasticode\Traits\Convert\ToBit;
use Plasticode\Util\Date;

/**
 * @emits AssociationScopeChangedEvent
 * @emits AssociationSeverityChangedEvent
 */
class AssociationRecountService
{
    use ToBit;

    private AssociationRepositoryInterface $associationRepository;
    private AssociationSpecification $associationSpecification;
    private EventDispatcher $eventDispatcher;

    public function __construct(
        AssociationRepositoryInterface $associationRepository,
        AssociationSpecification $associationSpecification,
        EventDispatcher $eventDispatcher
    )
    {
        $this->associationRepository = $associationRepository;
        $this->associationSpecification = $associationSpecification;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function recountAll(
        Association $association,
        ?Event $sourceEvent = null
    ): Association
    {
        $association = $this->recountSeverity($association, $sourceEvent);
        $association = $this->recountScope($association, $sourceEvent);
        $association = $this->recountMeta($association);

        return $association;
    }

    /**
     * Recounts all associations of the word.
     */
    public function recountByWord(Word $word, ?Event $sourceEvent = null): void
    {
        $word
            ->associations()
            ->apply(
                fn (Association $a) => $this->recountAll($a, $sourceEvent)
            );
    }

    public function recountScope(
        Association $association,
        ?Event $sourceEvent = null
    ): Association
    {
        $now = Date::dbNow();
        $changed = false;

        $scope = $this->associationSpecification->countScope($association);

        if ($association->scope != $scope) {
            $association->scope = $scope;
            $association->scopeUpdatedAt = $now;

            $changed = true;
        }

        $association->updatedAt = $now;

        $association = $this->associationRepository->save($association);

        if ($changed) {
            $this->eventDispatcher->dispatch(
                new AssociationScopeChangedEvent($association, $sourceEvent)
            );
        }

        return $association;
    }

    private function recountSeverity(
        Association $association,
        ?Event $sourceEvent = null
    ): Association
    {
        $now = Date::dbNow();
        $changed = false;

        $severity = $this->associationSpecification->countSeverity($association);

        if ($association->severity != $severity) {
            $association->severity = $severity;
            $association->severityUpdatedAt = $now;

            $changed = true;
        }

        $association->updatedAt = $now;

        $association = $this->associationRepository->save($association);

        if ($changed) {
            $this->eventDispatcher->dispatch(
                new AssociationSeverityChangedEvent($association, $sourceEvent)
            );
        }

        return $association;
    }

    public function recountMeta(Association $association): Association
    {
        $association->setMetaValue(
            Association::META_USAGE_COUNT,
            $association->usageCount(true)
        );

        return $this->associationRepository->save($association);
    }
}
