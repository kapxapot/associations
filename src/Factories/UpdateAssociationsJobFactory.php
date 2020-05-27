<?php

namespace App\Factories;

use App\Factories\Interfaces\DbModelCollectionJobFactoryInterface;
use App\Jobs\UpdateAssociationsJob;
use App\Repositories\Interfaces\AssociationRepositoryInterface;
use Plasticode\Core\Interfaces\SettingsProviderInterface;
use Plasticode\Events\EventDispatcher;

class UpdateAssociationsJobFactory implements DbModelCollectionJobFactoryInterface
{
    private \Closure $maker;

    public function __construct(
        AssociationRepositoryInterface $associationRepository,
        SettingsProviderInterface $settingsProvider,
        EventDispatcher $eventDispatcher
    )
    {
        $this->maker =
            fn () =>
            new UpdateAssociationsJob(
                $associationRepository,
                $settingsProvider,
                $eventDispatcher
            );
    }

    public function make() : UpdateAssociationsJob
    {
        return ($this->maker)();
    }
}
