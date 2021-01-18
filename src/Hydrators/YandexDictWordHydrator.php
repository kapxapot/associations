<?php

namespace App\Hydrators;

use App\Models\YandexDictWord;
use App\Repositories\Interfaces\LanguageRepositoryInterface;
use App\Repositories\Interfaces\WordRepositoryInterface;
use Plasticode\Hydrators\Generic\Hydrator;
use Plasticode\Models\Generic\DbModel;

class YandexDictWordHydrator extends Hydrator
{
    private LanguageRepositoryInterface $languageRepository;
    private WordRepositoryInterface $wordRepository;

    public function __construct(
        LanguageRepositoryInterface $languageRepository,
        WordRepositoryInterface $wordRepository
    )
    {
        $this->languageRepository = $languageRepository;
        $this->wordRepository = $wordRepository;
    }

    /**
     * @param YandexDictWord $entity
     */
    public function hydrate(DbModel $entity): YandexDictWord
    {
        return $entity
            ->withLanguage(
                fn () => $this->languageRepository->get($entity->languageId)
            )
            ->withLinkedWord(
                fn () => $this->wordRepository->get($entity->wordId)
            );
    }
}
