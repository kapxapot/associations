<?php

namespace App\Factories;

use App\Factories\Interfaces\ModelJobFactoryInterface;
use App\Jobs\UpdateAssociationsJob;
use App\Repositories\Interfaces\AssociationRepositoryInterface;
use Plasticode\Events\EventDispatcher;
use Plasticode\Settings\Interfaces\SettingsProviderInterface;

class UpdateAssociationsJobFactory implements ModelJobFactoryInterface
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
