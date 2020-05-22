<?php

namespace App\Factories;

use App\Jobs\UpdateWordsJob;
use App\Repositories\Interfaces\WordRepositoryInterface;
use Plasticode\Core\Interfaces\SettingsProviderInterface;
use Plasticode\Events\EventDispatcher;

class UpdateWordsJobFactory extends JobFactory
{
    private \Closure $maker;

    public function __construct(
        SettingsProviderInterface $settingsProvider,
        EventDispatcher $eventDispatcher,
        WordRepositoryInterface $wordRepository
    )
    {
        parent::__construct($settingsProvider, $eventDispatcher);

        $this->maker =
            fn () =>
            new UpdateWordsJob(
                $wordRepository,
                $this->settingsProvider,
                $this->eventDispatcher
            );
    }

    public function make() : UpdateWordsJob
    {
        return ($this->maker)();
    }
}
