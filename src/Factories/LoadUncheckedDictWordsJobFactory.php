<?php

namespace App\Factories;

use App\Factories\Interfaces\DbModelCollectionJobFactoryInterface;
use App\Jobs\LoadUncheckedDictWordsJob;
use App\Repositories\Interfaces\WordRepositoryInterface;
use App\Services\DictionaryService;
use Plasticode\Core\Interfaces\SettingsProviderInterface;

class LoadUncheckedDictWordsJobFactory implements DbModelCollectionJobFactoryInterface
{
    private \Closure $maker;

    public function __construct(
        WordRepositoryInterface $wordRepository,
        SettingsProviderInterface $settingsProvider,
        DictionaryService $dictionaryService
    )
    {
        $this->maker =
            fn () =>
            new LoadUncheckedDictWordsJob(
                $wordRepository,
                $settingsProvider,
                $dictionaryService
            );
    }

    public function make() : LoadUncheckedDictWordsJob
    {
        return ($this->maker)();
    }
}
