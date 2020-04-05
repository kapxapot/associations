<?php

namespace App\Services;

use App\External\YandexDict;
use App\Models\Language;
use App\Models\Word;
use App\Models\YandexDictWord;
use App\Models\Interfaces\DictWordInterface;
use App\Repositories\Interfaces\WordRepositoryInterface;
use App\Repositories\Interfaces\YandexDictWordRepositoryInterface;
use App\Services\Interfaces\ExternalDictServiceInterface;

class YandexDictService implements ExternalDictServiceInterface
{
    private WordRepositoryInterface $wordRepository;
    private YandexDictWordRepositoryInterface $yandexDictWordRepository;
    private YandexDict $yandexDict;

    public function __construct(
        WordRepositoryInterface $wordRepository,
        YandexDictWordRepositoryInterface $yandexDictWordRepository,
        YandexDict $yandexDict
    )
    {
        $this->wordRepository = $wordRepository;
        $this->yandexDictWordRepository = $yandexDictWordRepository;
        $this->yandexDict = $yandexDict;
    }

    public function getWord(Word $word) : ?DictWordInterface
    {
        return $this->get(
            $word->language(),
            $word->word,
            $word
        );
    }

    public function getWordStr(
        Language $language,
        string $wordStr
    ) : ?DictWordInterface
    {
        $word = $this->wordRepository->findInLanguage(
            $language,
            $wordStr
        );

        if (!is_null($word)) {
            return $this->getWord($word);
        }

        return $this->get($language, $wordStr);
    }

    private function get(
        Language $language,
        string $wordStr,
        Word $word = null
    ) : ?YandexDictWord
    {
        if (!$this->isLanguageSupported($language)) {
            return null;
        }

        // searching by word
        $dictWord = !is_null($word)
            ? $this->yandexDictWordRepository->getByWord($word)
            : null;
        
        // searching by language & wordStr
        $dictWord = $dictWord ??
            $this->yandexDictWordRepository->getByWordStr($language, $wordStr);

        if (is_null($dictWord)) {
            // no word found, loading from dictionary
            $dictWord = $this->loadFromDictionary($language, $wordStr, $word);

            if ($dictWord) {
                $dictWord = $this->yandexDictWordRepository->save($dictWord);
            }
        }

        return $dictWord;
    }

    private function isLanguageSupported(Language $language) : bool
    {
        return !is_null($language->yandexDictCode);
    }

    private function loadFromDictionary(
        Language $language,
        string $wordStr,
        Word $word = null
    ) : ?YandexDictWord
    {
        $result = $this->yandexDict->request(
            $language->yandexDictCode,
            $wordStr
        );

        if (strlen($result) == 0) {
            return null;
        }

        $dictWord = $this->yandexDictWordRepository->create(
            [
                'word' => $wordStr,
                'language_id' => $language->getId(),
                'response' => $result,
            ]
        );

        if (!is_null($word)) {
            $dictWord->wordId = $word->getId();
        }

        $data = $this->parseApiResult($result);
        $dictWord = $this->applyParsedData($dictWord, $data);

        return $dictWord;
    }

    private function parseApiResult(?string $result) : ?array
    {
        return is_null($result)
            ? null
            : json_decode($result, true);
    }

    private function applyParsedData(
        YandexDictWord $dictWord,
        ?array $data
    ) : YandexDictWord
    {
        if (is_array($data)) {
            $def = $data['def'][0] ?? null;
            $pos = $def['pos'] ?? null;

            $dictWord->pos = $pos;
        }

        return $dictWord;
    }
}
