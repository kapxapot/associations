<?php

namespace App\Hydrators;

use App\Auth\Interfaces\AuthInterface;
use App\Core\Interfaces\LinkerInterface;
use App\Models\Association;
use App\Repositories\Interfaces\AssociationFeedbackRepositoryInterface;
use App\Repositories\Interfaces\LanguageRepositoryInterface;
use App\Repositories\Interfaces\TurnRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\Interfaces\WordRepositoryInterface;
use Plasticode\Hydrators\Generic\Hydrator;
use Plasticode\Models\Generic\DbModel;

class AssociationHydrator extends Hydrator
{
    private AssociationFeedbackRepositoryInterface $associationFeedbackRepository;
    private LanguageRepositoryInterface $languageRepository;
    private TurnRepositoryInterface $turnRepository;
    private UserRepositoryInterface $userRepository;
    private WordRepositoryInterface $wordRepository;

    private AuthInterface $auth;
    private LinkerInterface $linker;

    public function __construct(
        AssociationFeedbackRepositoryInterface $associationFeedbackRepository,
        LanguageRepositoryInterface $languageRepository,
        TurnRepositoryInterface $turnRepository,
        UserRepositoryInterface $userRepository,
        WordRepositoryInterface $wordRepository,
        AuthInterface $auth,
        LinkerInterface $linker
    )
    {
        $this->associationFeedbackRepository = $associationFeedbackRepository;
        $this->languageRepository = $languageRepository;
        $this->turnRepository = $turnRepository;
        $this->userRepository = $userRepository;
        $this->wordRepository = $wordRepository;

        $this->auth = $auth;
        $this->linker = $linker;
    }

    /**
     * @param Association $entity
     */
    public function hydrate(DbModel $entity): Association
    {
        return $entity
            ->withFirstWord(
                fn () => $this->wordRepository->get($entity->firstWordId)
            )
            ->withSecondWord(
                fn () => $this->wordRepository->get($entity->secondWordId)
            )
            ->withFeedbacks(
                fn () =>
                $this
                    ->associationFeedbackRepository
                    ->getAllByAssociation($entity)
            )
            ->withUrl(
                fn () => $this->linker->association($entity)
            )
            ->withLanguage(
                fn () => $this->languageRepository->get($entity->languageId)
            )
            ->withTurns(
                fn () => $this->turnRepository->getAllByAssociation($entity)
            )
            ->withMe(
                fn () => $this->auth->getUser()
            )
            ->withCreator(
                fn () => $this->userRepository->get($entity->createdBy)
            );
    }
}
