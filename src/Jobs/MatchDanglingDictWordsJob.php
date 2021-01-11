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
                function (DictWordInterface $dictWord) {
                    $language = $dictWord->getLanguage();
                    $wordStr = $dictWord->getWord();

                    $word = $this->wordRepository->findInLanguage($language, $wordStr);

                    if (is_null($word)) {
                        return null;
                    }

                    return $this->dictionaryService->link($dictWord, $word);
                }
            )
            ->clean();

        return DictWordCollection::from($processed);
    }
}
