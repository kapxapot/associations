<?php

namespace App\Jobs;

use App\Events\AssociationOutOfDateEvent;
use App\Models\Association;
use Plasticode\Collection;
use Plasticode\Core\Interfaces\SettingsProviderInterface;
use Plasticode\Events\EventDispatcher;

class UpdateAssociationsJob
{
    /** @var SettingsProviderInterface */
    private $settingsProvider;

    /** @var EventDispatcher */
    private $dispatcher;

    public function __construct(
        SettingsProviderInterface $settingsProvider,
        EventDispatcher $dispatcher
    )
    {
        $this->settingsProvider = $settingsProvider;
        $this->dispatcher = $dispatcher;
    }

    public function run() : Collection
    {
        $limit = $this->settingsProvider
            ->get('associations.update.limit');
        
        $ttl = $this->settingsProvider
            ->get('associations.update.ttl_min');

        $outOfDate = Association::getOutOfDate($ttl)
            ->limit($limit)
            ->all();

        foreach ($outOfDate as $assoc) {
            $event = new AssociationOutOfDateEvent($assoc);
            $this->dispatcher->dispatch($event);
        }

        return $outOfDate;
    }
}
