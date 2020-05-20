<?php

namespace App\Hydrators;

use App\Models\YandexDictWord;
use App\Repositories\Interfaces\LanguageRepositoryInterface;
use Plasticode\Hydrators\Basic\Hydrator;
use Plasticode\Models\DbModel;

class YandexDictWordHydrator extends Hydrator
{
    private LanguageRepositoryInterface $languageRepository;

    public function __construct(
        LanguageRepositoryInterface $languageRepository
    )
    {
        $this->languageRepository = $languageRepository;
    }

    /**
     * @param YandexDictWord $entity
     */
    public function hydrate(DbModel $entity) : YandexDictWord
    {
        return $entity
            ->withLanguage(
                fn () => $this->languageRepository->get($entity->languageId)
            );
    }
}
