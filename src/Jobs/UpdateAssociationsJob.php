<?php

namespace App\Jobs;

use App\Collections\AssociationCollection;
use App\Events\Association\AssociationOutOfDateEvent;
use App\Jobs\Interfaces\ModelJobInterface;
use App\Models\Association;
use App\Repositories\Interfaces\AssociationRepositoryInterface;
use Plasticode\Events\Event;
use Plasticode\Events\EventDispatcher;
use Plasticode\Settings\Interfaces\SettingsProviderInterface;

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

    public function run(): AssociationCollection
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

        $outOfDate
            ->map(
                fn (Association $a) => new AssociationOutOfDateEvent($a)
            )
            ->apply(
                fn (Event $e) => $this->eventDispatcher->dispatch($e)
            );

        return $outOfDate;
    }
}
