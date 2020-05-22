<?php

namespace App\Jobs;

use App\Repositories\Interfaces\DictWordRepositoryInterface;
use Plasticode\Collections\Basic\DbModelCollection;
use Plasticode\Core\Interfaces\SettingsProviderInterface;
use Plasticode\Events\EventDispatcher;

class UpdateDictWordsJob
{
    private DictWordRepositoryInterface $dictWordRepository;

    private SettingsProviderInterface $settingsProvider;
    private EventDispatcher $eventDispatcher;

    public function __construct(
        DictWordRepositoryInterface $dictWordRepository,
        SettingsProviderInterface $settingsProvider,
        EventDispatcher $eventDispatcher
    )
    {
        $this->dictWordRepository = $dictWordRepository;

        $this->settingsProvider = $settingsProvider;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function run() : DbModelCollection
    {
        return DbModelCollection::empty();
    }
}
