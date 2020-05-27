<?php

namespace App\Jobs;

use App\Collections\DictWordCollection;
use App\Jobs\Interfaces\DbModelCollectionJobInterface;
use App\Models\Word;
use App\Repositories\Interfaces\WordRepositoryInterface;
use App\Services\DictionaryService;
use Plasticode\Core\Interfaces\SettingsProviderInterface;

/**
 * For all words without dict word load dict words.
 */
class LoadUncheckedDictWordsJob implements DbModelCollectionJobInterface
{
    private WordRepositoryInterface $wordRepository;
    private SettingsProviderInterface $settingsProvider;
    private DictionaryService $dictionaryService;

    public function __construct(
        WordRepositoryInterface $wordRepository,
        SettingsProviderInterface $settingsProvider,
        DictionaryService $dictionaryService
    )
    {
        $this->wordRepository = $wordRepository;
        $this->settingsProvider = $settingsProvider;
        $this->dictionaryService = $dictionaryService;
    }

    public function run() : DictWordCollection
    {
        $limit = $this
            ->settingsProvider
            ->get('jobs.load_unchecked_dict_words.batch_size', 10);

        return DictWordCollection::from(
            $this
                ->wordRepository
                ->getAllUnchecked($limit)
                ->map(
                    fn (Word $w) => $this->dictionaryService->getByWord($w, true)
                )
        );
    }
}
