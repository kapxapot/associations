<?php

namespace App\Services;

use App\Config\Interfaces\AssociationConfigInterface;
use App\Events\Association\AssociationApprovedChangedEvent;
use App\Events\Association\AssociationMatureChangedEvent;
use App\Events\Association\AssociationOutOfDateEvent;
use App\Events\Feedback\AssociationFeedbackCreatedEvent;
use App\Events\Turn\TurnCreatedEvent;
use App\Events\Word\WordMatureChangedEvent;
use App\Models\Association;
use App\Repositories\Interfaces\AssociationRepositoryInterface;
use Plasticode\Events\EventProcessor;
use Plasticode\Util\Convert;
use Plasticode\Util\Date;

class AssociationRecountService extends EventProcessor
{
    private AssociationRepositoryInterface $associationRepository;
    private AssociationConfigInterface $config;

    public function __construct(
        AssociationRepositoryInterface $associationRepository,
        AssociationConfigInterface $config
    )
    {
        $this->associationRepository = $associationRepository;
        $this->config = $config;
    }

    /**
     * TurnCreatedEvent event processing.
     */
    public function processTurnCreatedEvent(TurnCreatedEvent $event) : iterable
    {
        $assoc = $event->getTurn()->association();

        if ($assoc) {
            $assoc = $this->recountApproved($assoc);

            $assoc = $this->associationRepository->save($assoc);

            yield new AssociationApprovedChangedEvent($assoc);
        }
    }

    /**
     * WordMatureChangedEvent event processing.
     */
    public function processWordMatureChangedEvent(WordMatureChangedEvent $event) : iterable
    {
        $word = $event->getWord();

        foreach ($word->associations() as $assoc) {
            $assoc = $this->recountMature($assoc);

            $assoc = $this->associationRepository->save($assoc);

            yield new AssociationMatureChangedEvent($assoc);
        }
    }

    /**
     * AssociationFeedbackCreatedEvent event processing.
     */
    public function processAssociationFeedbackCreatedEvent(
        AssociationFeedbackCreatedEvent $event
    ) : iterable
    {
        $feedback = $event->getFeedback();
        $assoc = $feedback->association();

        return $this->recountAll($assoc);
    }

    /**
     * AssociationOutOfDateEvent event processing.
     */
    public function processAssociationOutOfDateEvent(
        AssociationOutOfDateEvent $event
    ) : iterable
    {
        $assoc = $event->getAssociation();
        return $this->recountAll($assoc);
    }

    private function recountAll(Association $assoc) : iterable
    {
        $assoc = $this->recountApproved($assoc);
        $assoc = $this->recountMature($assoc);

        $assoc = $this->associationRepository->save($assoc);

        yield new AssociationApprovedChangedEvent($assoc);
        yield new AssociationMatureChangedEvent($assoc);
    }

    private function recountApproved(Association $assoc) : Association
    {
        $usageCoeff = $this->config->associationUsageCoeff();
        $dislikeCoeff = $this->config->associationDislikeCoeff();
        $threshold = $this->config->associationApprovalThreshold();

        $turnsByUsers = $assoc->turns()->groupByUser();

        $turnCount = count($turnsByUsers);

        $dislikeCount = $assoc->dislikes()->count();

        $score = $turnCount * $usageCoeff - $dislikeCount * $dislikeCoeff;
        $approved = ($score >= $threshold);

        $now = Date::dbNow();

        if (
            $assoc->isApproved() !== $approved
            || is_null($assoc->approvedUpdatedAt)
        ) {
            $assoc->approved = Convert::toBit($approved);
            $assoc->approvedUpdatedAt = $now;
        }

        $assoc->updatedAt = $now;

        return $assoc;
    }

    private function recountMature(Association $assoc) : Association
    {
        if (
            $assoc->firstWord()->isMature()
            || $assoc->secondWord()->isMature()
        ) {
            $mature = true;
        } else {
            $threshold = $this->config->associationMatureThreshold();

            $maturesCount = $assoc->matures()->count();

            $mature = ($maturesCount >= $threshold);
        }

        $now = Date::dbNow();

        if (
            $assoc->isMature() !== $mature
            || is_null($assoc->matureUpdateAt)
        ) {
            $assoc->mature = Convert::toBit($mature);
            $assoc->matureUpdatedAt = $now;
        }

        $assoc->updatedAt = $now;

        return $assoc;
    }
}
