<?php

namespace App\Services;

use App\Config\Interfaces\AssociationConfigInterface;
use App\Events\AssociationApprovedEvent;
use App\Events\AssociationFeedbackEvent;
use App\Events\AssociationMatureEvent;
use App\Events\AssociationOutOfDateEvent;
use App\Events\NewTurnEvent;
use App\Events\WordMatureEvent;
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
     * NewTurnEvent event processing.
     */
    public function processNewTurnEvent(NewTurnEvent $event) : iterable
    {
        $assoc = $event->getTurn()->association();

        if ($assoc) {
            $assoc = $this->recountApproved($assoc);

            $assoc = $this->associationRepository->save($assoc);

            yield new AssociationApprovedEvent($assoc);
        }
    }

    /**
     * WordMatureEvent event processing.
     */
    public function processWordMatureEvent(WordMatureEvent $event) : iterable
    {
        $word = $event->getWord();

        foreach ($word->associations() as $assoc) {
            $assoc = $this->recountMature($assoc);

            $assoc = $this->associationRepository->save($assoc);

            yield new AssociationMatureEvent($assoc);
        }
    }

    /**
     * AssociationFeedbackEvent event processing.
     */
    public function processAssociationFeedbackEvent(
        AssociationFeedbackEvent $event
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

        yield new AssociationApprovedEvent($assoc);
        yield new AssociationMatureEvent($assoc);
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
