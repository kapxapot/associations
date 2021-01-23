<?php

namespace App\Services;

use App\Events\Association\AssociationApprovedChangedEvent;
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

    public function recountAll(Association $assoc, ?Event $sourceEvent = null) : Association
    {
        $assoc = $this->recountApproved($assoc, $sourceEvent);
        $assoc = $this->recountMature($assoc, $sourceEvent);

        return $assoc;
    }

    public function recountApproved(
        Association $assoc,
        ?Event $sourceEvent = null
    ) : Association
    {
        $now = Date::dbNow();
        $changed = false;

        $approved = $this->associationSpecification->isApproved($assoc);

        if (
            $assoc->isApproved() !== $approved
            || is_null($assoc->approvedUpdatedAt)
        ) {
            $assoc->approved = self::toBit($approved);
            $assoc->approvedUpdatedAt = $now;

            $changed = true;
        }

        $assoc->updatedAt = $now;

        $assoc = $this->associationRepository->save($assoc);

        if ($changed) {
            $this->eventDispatcher->dispatch(
                new AssociationApprovedChangedEvent($assoc, $sourceEvent)
            );
        }

        return $assoc;
    }

    public function recountMature(
        Association $assoc,
        ?Event $sourceEvent = null
    ) : Association
    {
        $now = Date::dbNow();
        $changed = false;

        $mature = $this->associationSpecification->isMature($assoc);

        if (
            $assoc->isMature() !== $mature
            || is_null($assoc->matureUpdateAt)
        ) {
            $assoc->mature = self::toBit($mature);
            $assoc->matureUpdatedAt = $now;

            $changed = true;
        }

        $assoc->updatedAt = $now;

        $assoc = $this->associationRepository->save($assoc);

        if ($changed) {
            $this->eventDispatcher->dispatch(
                new AssociationMatureChangedEvent($assoc, $sourceEvent)
            );
        }

        return $assoc;
    }
}
