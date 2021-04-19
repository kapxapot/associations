<?php

namespace App\Services;

use App\Events\Association\AssociationApprovedChangedEvent;
use App\Events\Association\AssociationDisabledChangedEvent;
use App\Events\Association\AssociationMatureChangedEvent;
use App\Models\Association;
use App\Repositories\Interfaces\AssociationRepositoryInterface;
use App\Specifications\AssociationSpecification;
use Plasticode\Events\Event;
use Plasticode\Events\EventDispatcher;
use Plasticode\Traits\Convert\ToBit;
use Plasticode\Util\Date;

/**
 * @emits AssociationApprovedChangedEvent
 * @emits AssociationDisabledChangedEvent
 * @emits AssociationMatureChangedEvent
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
        $assoc = $this->recountDisabled($association, $sourceEvent);
        $assoc = $this->recountApproved($association, $sourceEvent);
        $assoc = $this->recountMature($association, $sourceEvent);

        return $assoc;
    }

    public function recountDisabled(
        Association $association,
        ?Event $sourceEvent = null
    ): Association
    {
        $now = Date::dbNow();
        $changed = false;

        $disabled = $this->associationSpecification->isDisabled($association);

        if ($association->isDisabled() !== $disabled) {
            $association->disabled = self::toBit($disabled);
            $association->disabledUpdatedAt = $now;

            $changed = true;
        }

        $association->updatedAt = $now;

        $association = $this->associationRepository->save($association);

        if ($changed) {
            $this->eventDispatcher->dispatch(
                new AssociationDisabledChangedEvent($association, $sourceEvent)
            );
        }

        return $association;
    }

    public function recountApproved(
        Association $association,
        ?Event $sourceEvent = null
    ): Association
    {
        $now = Date::dbNow();
        $changed = false;

        $approved = $this->associationSpecification->isApproved($association);

        if ($association->isApproved() !== $approved) {
            $association->approved = self::toBit($approved);
            $association->approvedUpdatedAt = $now;

            $changed = true;
        }

        $association->updatedAt = $now;

        $association = $this->associationRepository->save($association);

        if ($changed) {
            $this->eventDispatcher->dispatch(
                new AssociationApprovedChangedEvent($association, $sourceEvent)
            );
        }

        return $association;
    }

    public function recountMature(
        Association $association,
        ?Event $sourceEvent = null
    ): Association
    {
        $now = Date::dbNow();
        $changed = false;

        $mature = $this->associationSpecification->isMature($association);

        if ($association->isMature() !== $mature) {
            $association->mature = self::toBit($mature);
            $association->matureUpdatedAt = $now;

            $changed = true;
        }

        $association->updatedAt = $now;

        $association = $this->associationRepository->save($association);

        if ($changed) {
            $this->eventDispatcher->dispatch(
                new AssociationMatureChangedEvent($association, $sourceEvent)
            );
        }

        return $association;
    }
}
