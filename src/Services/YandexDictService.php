<?php

namespace App\Services;

use Plasticode\Contained;

use App\Models\Language;
use App\Models\Word;
use App\Models\YandexDictWord;

class YandexDictService extends Contained
{
    public function getWord(Word $word) : ?YandexDictWord
    {
        return $this->get($word->language(), $word->word, $word);
    }

    public function getWordStr(Language $language, string $wordStr) : ?YandexDictWord
    {
        return $this->get($language, $wordStr);
    }

    private function get(Language $language, string $wordStr, Word $word = null) : ?YandexDictWord
    {
        if (!$this->isLanguageSupported($language)) {
            return null;
        }

        // searching by word
        $dictWord = !is_null($word)
            ? YandexDictWord::getByWord($word)
            : null;
        
        // searching by language & wordStr
        $dictWord = $dictWord ??
            YandexDictWord::getByWordStr($language, $wordStr);

        if (is_null($dictWord)) {
            // no word found, loading from dictionary
            $dictWord = $this->loadFromDictionary($language, $wordStr, $word);

            if (!is_null($dictWord)) {
                $dictWord->save();
            }
        }

        return $dictWord;
    }

    private function isLanguageSupported(Language $language) : bool
    {
        return !is_null($language->yandexDictCode);
    }

    private function loadFromDictionary(Language $language, string $wordStr, Word $word = null) : ?YandexDictWord
    {
        $dictWord = YandexDictWord::create([
            'word' => $wordStr,
            'language_id' => $language->getId(),
        ]);

        if (!is_null($word)) {
            $dictWord->wordId = $word->getId();
        }

        $result = $this->yandexDict->request($language->yandexDictCode, $wordStr);

        $dictWord->response = $result;

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

    private function applyParsedData(YandexDictWord $dictWord, ?array $data) : YandexDictWord
    {
        if (is_array($data)) {
            $def = $data['def'][0] ?? null;
            $pos = $def['pos'] ?? null;

            $dictword->pos = $pos;
        }

        return $dictWord;
    }
}
