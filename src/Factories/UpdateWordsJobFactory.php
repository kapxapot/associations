<?php

namespace App\Factories;

use App\Factories\Interfaces\DbModelCollectionJobFactoryInterface;
use App\Jobs\UpdateWordsJob;
use App\Repositories\Interfaces\WordRepositoryInterface;
use Plasticode\Core\Interfaces\SettingsProviderInterface;
use Plasticode\Events\EventDispatcher;

class UpdateWordsJobFactory implements DbModelCollectionJobFactoryInterface
{
    private \Closure $maker;

    public function __construct(
        WordRepositoryInterface $wordRepository,
        SettingsProviderInterface $settingsProvider,
        EventDispatcher $eventDispatcher
    )
    {
        $this->maker =
            fn () =>
            new UpdateWordsJob(
                $wordRepository,
                $settingsProvider,
                $eventDispatcher
            );
    }

    public function make() : UpdateWordsJob
    {
        return ($this->maker)();
    }
}
