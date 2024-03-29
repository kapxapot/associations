<?php

namespace App\Hydrators;

use App\Auth\Interfaces\AuthInterface;
use App\Core\Interfaces\LinkerInterface;
use App\Models\Association;
use App\Repositories\Interfaces\AssociationFeedbackRepositoryInterface;
use App\Repositories\Interfaces\AssociationOverrideRepositoryInterface;
use App\Repositories\Interfaces\LanguageRepositoryInterface;
use App\Repositories\Interfaces\TurnRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\Interfaces\WordRepositoryInterface;
use App\Services\AssociationService;
use Plasticode\Hydrators\Generic\Hydrator;
use Plasticode\Models\Generic\DbModel;

class AssociationHydrator extends Hydrator
{
    private AssociationFeedbackRepositoryInterface $associationFeedbackRepository;
    private AssociationOverrideRepositoryInterface $associationOverrideRepository;
    private LanguageRepositoryInterface $languageRepository;
    private TurnRepositoryInterface $turnRepository;
    private UserRepositoryInterface $userRepository;
    private WordRepositoryInterface $wordRepository;

    private AssociationService $associationService;

    private AuthInterface $auth;
    private LinkerInterface $linker;

    public function __construct(
        AssociationFeedbackRepositoryInterface $associationFeedbackRepository,
        AssociationOverrideRepositoryInterface $associationOverrideRepository,
        LanguageRepositoryInterface $languageRepository,
        TurnRepositoryInterface $turnRepository,
        UserRepositoryInterface $userRepository,
        WordRepositoryInterface $wordRepository,
        AssociationService $associationService,
        AuthInterface $auth,
        LinkerInterface $linker
    )
    {
        $this->associationFeedbackRepository = $associationFeedbackRepository;
        $this->associationOverrideRepository = $associationOverrideRepository;
        $this->languageRepository = $languageRepository;
        $this->turnRepository = $turnRepository;
        $this->userRepository = $userRepository;
        $this->wordRepository = $wordRepository;

        $this->associationService = $associationService;

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
                $this->frozen(
                    fn () => $this->wordRepository->get($entity->firstWordId)
                )
            )
            ->withSecondWord(
                $this->frozen(
                    fn () => $this->wordRepository->get($entity->secondWordId)
                )
            )
            ->withCanonical(
                $this->frozen(
                    fn () => $this->associationService->getCanonical($entity)
                )
            )
            ->withCanonicalForMe(
                fn () => $this->associationService->getCanonicalPlayableAgainst(
                    $entity,
                    $entity->me()
                )
            )
            ->withFeedbacks(
                $this->frozen(
                    fn () => $this
                        ->associationFeedbackRepository
                        ->getAllByAssociation($entity)
                )
            )
            ->withUrl(
                fn () => $this->linker->association($entity)
            )
            ->withLanguage(
                fn () => $this->languageRepository->get($entity->languageId)
            )
            ->withTurns(
                $this->frozen(
                    fn () => $this->turnRepository->getAllByAssociation($entity)
                )
            )
            ->withMe(
                fn () => $this->auth->getUser()
            )
            ->withOverrides(
                $this->frozen(
                    fn () => $this->associationOverrideRepository->getAllByAssociation($entity)
                )
            )
            ->withCreator(
                fn () => $this->userRepository->get($entity->createdBy)
            );
    }
}
