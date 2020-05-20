<?php

namespace App\Services;

use App\Models\Interfaces\DictWordInterface;
use App\Models\Language;
use App\Models\Word;
use App\Repositories\Interfaces\DictWordRepositoryInterface;
use App\Repositories\Interfaces\WordRepositoryInterface;
use App\Services\Interfaces\ExternalDictServiceInterface;

class DictionaryService
{
    private DictWordRepositoryInterface $dictWordRepository;
    private WordRepositoryInterface $wordRepository;

    private ExternalDictServiceInterface $externalDictService;

    public function __construct(
        DictWordRepositoryInterface $dictWordRepository,
        WordRepositoryInterface $wordRepository,
        ExternalDictServiceInterface $externalDictService
    )
    {
        $this->dictWordRepository = $dictWordRepository;
        $this->wordRepository = $wordRepository;

        $this->externalDictService = $externalDictService;
    }

    public function isWordKnown(Word $word) : bool
    {
        $dictWord = $this->getByWord($word);

        return !is_null($dictWord) && $dictWord->isValid();
    }

    public function isWordStrKnown(Language $language, string $wordStr) : bool
    {
        $dictWord = $this->getByWordStr($language, $wordStr);

        return !is_null($dictWord) && $dictWord->isValid();
    }

    /**
     * Returns external dictionary word by language and word string.
     * 
     * @param $allowRemoteLoad Set this to 'true' if the remote loading must
     * be enabled. By default it's not performed.
     */
    public function getByWordStr(
        Language $language,
        string $wordStr,
        bool $allowRemoteLoad = false
    ) : ?DictWordInterface
    {
        return $this->get($language, $wordStr, null, $allowRemoteLoad);
    }

    /**
     * Returns external dictionary word by Word entity.
     * 
     * @param $allowRemoteLoad Set this to 'true' if the remote loading must
     * be enabled. By default it's not performed.
     */
    public function getByWord(
        Word $word,
        bool $allowRemoteLoad = false
    ) : ?DictWordInterface
    {
        return $this->get(null, null, $word, $allowRemoteLoad);
    }

    private function get(
        ?Language $language = null,
        ?string $wordStr = null,
        ?Word $word = null,
        bool $allowRemoteLoad = false
    ) : ?DictWordInterface
    {
        // trying to find word by wordStr
        $word ??= $this->wordRepository->findInLanguage(
            $language,
            $wordStr
        );

        // searching by word
        $dictWord = $word
            ? $this->dictWordRepository->getByWord($word)
            : null;

        // searching by language & wordStr
        $dictWord ??= ($language && strlen($wordStr) > 0)
            ? $this->dictWordRepository->getByWordStr($language, $wordStr)
            : null;

        if (is_null($dictWord) && $allowRemoteLoad) {
            // no word found, loading from dictionary
            $dictWord = $this
                ->externalDictService
                ->loadFromDictionary($language, $wordStr);

            if ($dictWord) {
                if ($word) {
                    $dictWord->wordId = $word->getId();
                }

                $dictWord = $this->dictWordRepository->save($dictWord);
            }
        }

        return $dictWord;
    }
}
