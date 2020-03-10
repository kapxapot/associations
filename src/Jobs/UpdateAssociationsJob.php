<?php

namespace App\Jobs;

use App\Events\AssociationOutOfDateEvent;
use App\Models\Association;
use Plasticode\Collection;
use Plasticode\Events\EventDispatcher;
use Plasticode\Interfaces\SettingsProviderInterface;

class UpdateAssociationsJob
{
    /** @var SettingsProviderInterface */
    private $settingsProvider;

    /** @var EventDispatcher */
    private $eventDispatcher;

    public function __construct(
        SettingsProviderInterface $settingsProvider,
        EventDispatcher $eventDispatcher
    )
    {
        $this->settingsProvider = $settingsProvider;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function run() : Collection
    {
        $limit = $this->settingsProvider->getSettings('associations.update.limit');
        $ttl = $this->settingsProvider->getSettings('associations.update.ttl_min');

        $outOfDate = Association::getOutOfDate($ttl)
            ->limit($limit)
            ->all();

        foreach ($outOfDate as $assoc) {
            $event = new AssociationOutOfDateEvent($assoc);
            $this->eventDispatcher->dispatch($event);
        }

        return $outOfDate;
    }
}
