<?php

namespace App\Jobs;

use App\Collections\DictWordCollection;
use App\Jobs\Interfaces\DbModelCollectionJobInterface;
use App\Models\Interfaces\DictWordInterface;
use App\Repositories\Interfaces\DictWordRepositoryInterface;
use App\Repositories\Interfaces\WordRepositoryInterface;
use Plasticode\Core\Interfaces\SettingsProviderInterface;

/**
 * For all dict words without word try to find word and map if it exists.
 */
class MatchDanglingDictWordsJob implements DbModelCollectionJobInterface
{
    private DictWordRepositoryInterface $dictWordRepository;
    private WordRepositoryInterface $wordRepository;
    private SettingsProviderInterface $settingsProvider;

    public function __construct(
        DictWordRepositoryInterface $dictWordRepository,
        WordRepositoryInterface $wordRepository,
        SettingsProviderInterface $settingsProvider
    )
    {
        $this->dictWordRepository = $dictWordRepository;
        $this->wordRepository = $wordRepository;
        $this->settingsProvider = $settingsProvider;
    }

    public function run() : DictWordCollection
    {
        $danglingTtl = $this
            ->settingsProvider
            ->get('jobs.match_dangling_dict_words.ttl_min');

        $limit = $this
            ->settingsProvider
            ->get('jobs.match_dangling_dict_words.batch_size');

        $processed = $this
            ->dictWordRepository
            ->getAllDanglingOutOfDate($danglingTtl, $limit)
            ->map(
                function (DictWordInterface $dw) {
                    $language = $dw->getLanguage();
                    $wordStr = $dw->getWord();

                    $word = $this->wordRepository->findInLanguage($language, $wordStr);

                    if (is_null($word)) {
                        return null;
                    }

                    $dw->wordId = $word->getId();
                    $this->dictWordRepository->save($dw);

                    return $dw;
                }
            );

        return DictWordCollection::from(
            $processed->clean()
        );
    }
}
