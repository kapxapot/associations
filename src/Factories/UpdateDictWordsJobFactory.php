<?php

namespace App\Factories;

use App\Jobs\UpdateDictWordsJob;
use App\Repositories\Interfaces\DictWordRepositoryInterface;
use Plasticode\Core\Interfaces\SettingsProviderInterface;
use Plasticode\Events\EventDispatcher;

class UpdateDictWordsJobFactory extends JobFactory
{
    private \Closure $maker;

    public function __construct(
        SettingsProviderInterface $settingsProvider,
        EventDispatcher $eventDispatcher,
        DictWordRepositoryInterface $dictWordRepository
    )
    {
        parent::__construct($settingsProvider, $eventDispatcher);

        $this->maker =
            fn () =>
            new UpdateDictWordsJob(
                $dictWordRepository,
                $this->settingsProvider,
                $this->eventDispatcher
            );
    }

    public function make() : UpdateDictWordsJob
    {
        return ($this->maker)();
    }
}
