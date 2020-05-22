<?php

namespace App\Jobs;

use App\Collections\AssociationCollection;
use App\Events\AssociationOutOfDateEvent;
use App\Repositories\Interfaces\AssociationRepositoryInterface;
use Plasticode\Core\Interfaces\SettingsProviderInterface;
use Plasticode\Events\EventDispatcher;

class UpdateAssociationsJob
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
        $ttl = $this->settingsProvider
            ->get('associations.update.ttl_min');

        $limit = $this->settingsProvider
            ->get('associations.update.limit');

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
