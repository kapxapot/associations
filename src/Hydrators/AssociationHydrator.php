<?php

namespace App\Hydrators;

use App\Core\Interfaces\LinkerInterface;
use App\Models\Association;
use App\Repositories\Interfaces\AssociationFeedbackRepositoryInterface;
use App\Repositories\Interfaces\LanguageRepositoryInterface;
use App\Repositories\Interfaces\TurnRepositoryInterface;
use App\Repositories\Interfaces\WordRepositoryInterface;
use Plasticode\Hydrators\Interfaces\HydratorInterface;
use Plasticode\Models\DbModel;

class AssociationHydrator implements HydratorInterface
{
    private AssociationFeedbackRepositoryInterface $associationFeedbackRepository;
    private LanguageRepositoryInterface $languageRepository;
    private TurnRepositoryInterface $turnRepository;
    private WordRepositoryInterface $wordRepository;
    private LinkerInterface $linker;

    public function __construct(
        AssociationFeedbackRepositoryInterface $associationFeedbackRepository,
        LanguageRepositoryInterface $languageRepository,
        TurnRepositoryInterface $turnRepository,
        WordRepositoryInterface $wordRepository,
        LinkerInterface $linker
    )
    {
        $this->associationFeedbackRepository = $associationFeedbackRepository;
        $this->languageRepository = $languageRepository;
        $this->turnRepository = $turnRepository;
        $this->wordRepository = $wordRepository;
        $this->linker = $linker;
    }

    /**
     * @param Association $entity
     */
    protected function hydrate(DbModel $entity) : Association
    {
        return $entity
            ->withFirstWord(
                $this->wordRepository->get($entity->firstWordId)
            )
            ->withSecondWord(
                $this->wordRepository->get($entity->secondWordId)
            )
            ->withUrl(
                $this->linker->association($entity)
            )
            ->withLanguage(
                $this->languageRepository->get($entity->languageId)
            )
            ->withTurns(
                $this->turnRepository->getAllByAssociation($entity)
            )
            ->withFeedbacks(
                $this->associationFeedbackRepository->getAllByAssociation($entity)
            );
    }
}
