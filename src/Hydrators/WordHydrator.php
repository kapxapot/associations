<?php

namespace App\Hydrators;

use App\Core\Interfaces\LinkerInterface;
use App\Models\Word;
use App\Repositories\Interfaces\AssociationRepositoryInterface;
use App\Repositories\Interfaces\LanguageRepositoryInterface;
use App\Repositories\Interfaces\TurnRepositoryInterface;
use App\Repositories\Interfaces\WordFeedbackRepositoryInterface;
use Plasticode\Hydrators\Interfaces\HydratorInterface;
use Plasticode\Models\DbModel;

class WordHydrator implements HydratorInterface
{
    private AssociationRepositoryInterface $associationRepository;
    private LanguageRepositoryInterface $languageRepository;
    private TurnRepositoryInterface $turnRepository;
    private WordFeedbackRepositoryInterface $wordFeedbackRepository;
    private LinkerInterface $linker;

    public function __construct(
        AssociationRepositoryInterface $associationRepository,
        LanguageRepositoryInterface $languageRepository,
        TurnRepositoryInterface $turnRepository,
        WordFeedbackRepositoryInterface $wordFeedbackRepository,
        LinkerInterface $linker
    )
    {
        $this->associationRepository = $associationRepository;
        $this->languageRepository = $languageRepository;
        $this->turnRepository = $turnRepository;
        $this->wordFeedbackRepository = $wordFeedbackRepository;
        $this->linker = $linker;
    }

    /**
     * @param Word $entity
     */
    public function hydrate(DbModel $entity) : Word
    {
        return $entity
            ->withAssociations(
                $this->associationRepository->getAllByWord($entity)
            )
            ->withFeedbacks(
                $this->wordFeedbackRepository->getAllByWord($entity)
            )
            ->withUrl(
                $this->linker->word($entity)
            )
            ->withLanguage(
                $this->languageRepository->get($entity->languageId)
            )
            ->withTurns(
                $this->turnRepository->getAllByWord($entity)
            );
    }
}
