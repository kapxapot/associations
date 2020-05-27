<?php

namespace App\Factories;

use App\Factories\Interfaces\DbModelCollectionJobFactoryInterface;
use App\Jobs\MatchDanglingDictWordsJob;
use App\Repositories\Interfaces\DictWordRepositoryInterface;
use App\Repositories\Interfaces\WordRepositoryInterface;
use Plasticode\Core\Interfaces\SettingsProviderInterface;

class MatchDanglingDictWordsJobFactory implements DbModelCollectionJobFactoryInterface
{
    private \Closure $maker;

    public function __construct(
        DictWordRepositoryInterface $dictWordRepository,
        WordRepositoryInterface $wordRepository,
        SettingsProviderInterface $settingsProvider
    )
    {
        $this->maker =
            fn () =>
            new MatchDanglingDictWordsJob(
                $dictWordRepository,
                $wordRepository,
                $settingsProvider
            );
    }

    public function make() : MatchDanglingDictWordsJob
    {
        return ($this->maker)();
    }
}
