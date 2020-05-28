<?php

namespace App\Jobs;

use App\Collections\AssociationCollection;
use App\Events\Association\AssociationOutOfDateEvent;
use App\Jobs\Interfaces\ModelJobInterface;
use App\Repositories\Interfaces\AssociationRepositoryInterface;
use Plasticode\Core\Interfaces\SettingsProviderInterface;
use Plasticode\Events\EventDispatcher;

class UpdateAssociationsJob implements ModelJobInterface
{
    private AssociationRepositoryInterface $associationRepository;

    private SettingsProviderInterface $settingsProvider;
    private EventDispatcher $eventDispatcher;

    public function __construct(
        AssociationRepositoryInterface $associationRepository,
        SettingsProviderInterface $settingsProvider,
        EventDispatcher $eventDispatcher
    )
    {
        $this->associationRepository = $associationRepository;

        $this->settingsProvider = $settingsProvider;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function run() : AssociationCollection
    {
        $ttl = $this
            ->settingsProvider
            ->get('jobs.update_associations.ttl_min', 1440);

        $limit = $this
            ->settingsProvider
            ->get('jobs.update_associations.batch_size', 10);

        $outOfDate = $this
            ->associationRepository
            ->getAllOutOfDate($ttl, $limit);

        foreach ($outOfDate as $assoc) {
            $event = new AssociationOutOfDateEvent($assoc);
            $this->eventDispatcher->dispatch($event);
        }

        return $outOfDate;
    }
}
