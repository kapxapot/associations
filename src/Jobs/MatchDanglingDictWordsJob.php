<?php

namespace App\Jobs;

use App\Collections\DictWordCollection;
use App\Jobs\Interfaces\ModelJobInterface;
use App\Models\Interfaces\DictWordInterface;
use App\Repositories\Interfaces\DictWordRepositoryInterface;
use App\Repositories\Interfaces\WordRepositoryInterface;
use App\Services\DictionaryService;
use Plasticode\Settings\Interfaces\SettingsProviderInterface;

/**
 * For all dict words without word try to find word and map if it exists.
 */
class MatchDanglingDictWordsJob implements ModelJobInterface
{
    private DictWordRepositoryInterface $dictWordRepository;
    private WordRepositoryInterface $wordRepository;

    private DictionaryService $dictionaryService;

    private SettingsProviderInterface $settingsProvider;

    public function __construct(
        DictWordRepositoryInterface $dictWordRepository,
        WordRepositoryInterface $wordRepository,
        DictionaryService $dictionaryService,
        SettingsProviderInterface $settingsProvider
    )
    {
        $this->dictWordRepository = $dictWordRepository;
        $this->wordRepository = $wordRepository;

        $this->dictionaryService = $dictionaryService;

        $this->settingsProvider = $settingsProvider;
    }

    public function run(): DictWordCollection
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
            ->cleanMap(
                function (DictWordInterface $dictWord) {
                    $language = $dictWord->getLanguage();
                    $wordStr = $dictWord->getWord();

                    // !attention!
                    // here we must find the word strictly by its current word string,
                    // not by the original word string
                    $word = $this->wordRepository->findInLanguageStrict($language, $wordStr);

                    if ($word === null) {
                        return null;
                    }

                    return $this->dictionaryService->link($dictWord, $word);
                }
            );

        return DictWordCollection::from($processed);
    }
}
