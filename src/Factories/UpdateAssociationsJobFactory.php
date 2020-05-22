<?php

namespace App\Factories;

use App\Jobs\UpdateAssociationsJob;
use App\Repositories\Interfaces\AssociationRepositoryInterface;
use Plasticode\Core\Interfaces\SettingsProviderInterface;
use Plasticode\Events\EventDispatcher;

class UpdateAssociationsJobFactory extends JobFactory
{
    private \Closure $maker;

    public function __construct(
        SettingsProviderInterface $settingsProvider,
        EventDispatcher $eventDispatcher,
        AssociationRepositoryInterface $associationRepository
    )
    {
        parent::__construct($settingsProvider, $eventDispatcher);

        $this->maker =
            fn () =>
            new UpdateAssociationsJob(
                $associationRepository,
                $this->settingsProvider,
                $this->eventDispatcher
            );
    }

    public function make() : UpdateAssociationsJob
    {
        return ($this->maker)();
    }
}
