<?php

namespace App\Repositories;

use App\Models\Language;
use App\Models\Word;
use App\Models\YandexDictWord;
use App\Repositories\Interfaces\YandexDictWordRepositoryInterface;
use App\Repositories\Traits\WithLanguageRepository;
use Plasticode\Repositories\Idiorm\Basic\IdiormRepository;

class YandexDictWordRepository extends IdiormRepository implements YandexDictWordRepositoryInterface
{
    use WithLanguageRepository;

    protected string $entityClass = YandexDictWord::class;

    public function create(array $data) : YandexDictWord
    {
        return $this->createEntity($data);
    }

    public function save(YandexDictWord $word) : YandexDictWord
    {
        return $this->saveEntity($word);
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
