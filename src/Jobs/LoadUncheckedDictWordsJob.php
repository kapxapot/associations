<?php

namespace App\Jobs;

use App\Collections\DictWordCollection;
use App\Jobs\Interfaces\ModelJobInterface;
use App\Models\Word;
use App\Repositories\Interfaces\WordRepositoryInterface;
use App\Services\DictionaryService;
use Plasticode\Settings\Interfaces\SettingsProviderInterface;

/**
 * For all words without dict word load dict words.
 */
class LoadUncheckedDictWordsJob implements ModelJobInterface
{
    private WordRepositoryInterface $wordRepository;
    private DictionaryService $dictionaryService;
    private SettingsProviderInterface $settingsProvider;

    public function __construct(
        WordRepositoryInterface $wordRepository,
        DictionaryService $dictionaryService,
        SettingsProviderInterface $settingsProvider
    )
    {
        $this->wordRepository = $wordRepository;
        $this->dictionaryService = $dictionaryService;
        $this->settingsProvider = $settingsProvider;
    }

    public function run(): DictWordCollection
    {
        $limit = $this
            ->settingsProvider
            ->get('jobs.load_unchecked_dict_words.batch_size', 10);

        return DictWordCollection::from(
            $this
                ->wordRepository
                ->getAllUnchecked($limit)
                ->cleanMap(
                    fn (Word $w) => $this->dictionaryService->loadByWord($w)
                )
        );
    }
}
