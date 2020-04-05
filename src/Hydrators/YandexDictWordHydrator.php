<?php

namespace App\Hydrators;

use App\Models\YandexDictWord;
use App\Repositories\Interfaces\LanguageRepositoryInterface;
use App\Repositories\Interfaces\WordRepositoryInterface;
use Plasticode\Hydrators\Interfaces\HydratorInterface;
use Plasticode\Models\DbModel;

class YandexDictWordHydrator implements HydratorInterface
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
    public function hydrate(DbModel $entity) : YandexDictWord
    {
        return $entity
            ->withWordEntity(
                $this->wordRepository->get($entity->wordId)
            )
            ->withLanguage(
                $this->languageRepository->get($entity->languageId)
            );
    }
}
