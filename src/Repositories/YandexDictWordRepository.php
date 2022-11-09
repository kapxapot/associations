<?php

namespace App\Repositories;

use App\Collections\DictWordCollection;
use App\Models\Interfaces\DictWordInterface;
use App\Models\Language;
use App\Models\Word;
use App\Models\YandexDictWord;
use App\Repositories\Generic\Repository;
use App\Repositories\Interfaces\YandexDictWordRepositoryInterface;
use App\Repositories\Traits\WithLanguageRepository;
use App\Repositories\Traits\WithWordRepository;
use Webmozart\Assert\Assert;

class YandexDictWordRepository extends Repository implements YandexDictWordRepositoryInterface
{
    use WithLanguageRepository;
    use WithWordRepository;

    protected function entityClass(): string
    {
        return YandexDictWord::class;
    }

    public function get(?int $id): ?YandexDictWord
    {
        return $this->getEntity($id);
    }

    public function create(array $data): YandexDictWord
    {
        return $this->createEntity($data);
    }

    /**
     * @param YandexDictWord $dictWord
     */
    public function save(DictWordInterface $dictWord): YandexDictWord
    {
        Assert::isInstanceOf($dictWord, YandexDictWord::class);

        if (!$dictWord->isPersisted()) {
            $dictWord['word_bin'] = $dictWord->word;
        }

        return $this->saveEntity($dictWord);
    }

    /**
     * Returns dict words without associated words that need to be updated.
     *
     * @param integer $ttlMin Update time-to-live in minutes.
     */
    public function getAllDanglingOutOfDate(int $ttlMin, int $limit = 0): DictWordCollection
    {
        return DictWordCollection::from(
            $this
                ->query()
                ->whereNull($this->wordIdField)
                ->whereRaw(
                    '(updated_at < date_sub(now(), interval ' . $ttlMin . ' minute))'
                )
                ->limit($limit)
                ->orderByAsc('updated_at')
        );
    }

    public function getByWord(Word $word): ?YandexDictWord
    {
        return $this->byWordQuery($word)->one();
    }

    public function getByWordStr(
        Language $language,
        string $wordStr
    ): ?YandexDictWord
    {
        return $this
            ->byLanguageQuery($language)
            ->where('word_bin', $wordStr)
            ->one();
    }
}
