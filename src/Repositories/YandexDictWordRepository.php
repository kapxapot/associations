<?php

namespace App\Repositories;

use App\Models\Interfaces\DictWordInterface;
use App\Models\Language;
use App\Models\Word;
use App\Models\YandexDictWord;
use App\Repositories\Interfaces\YandexDictWordRepositoryInterface;
use App\Repositories\Traits\WithLanguageRepository;
use Plasticode\Repositories\Idiorm\Basic\IdiormRepository;
use Webmozart\Assert\Assert;

class YandexDictWordRepository extends IdiormRepository implements YandexDictWordRepositoryInterface
{
    use WithLanguageRepository;

    protected string $entityClass = YandexDictWord::class;

    /**
     * @param YandexDictWord $dictWord
     */
    public function save(DictWordInterface $dictWord) : YandexDictWord
    {
        Assert::isInstanceOf($dictWord, YandexDictWord::class);

        return $this->saveEntity($dictWord);
    }

    public function getByWord(Word $word) : ?YandexDictWord
    {
        return $this
            ->query()
            ->where('word_id', $word->getId())
            ->one();
    }

    public function getByWordStr(
        Language $language,
        string $wordStr
    ) : ?YandexDictWord
    {
        return $this
            ->getByLanguageQuery($language)
            ->where('word', $wordStr)
            ->one();
    }
}
