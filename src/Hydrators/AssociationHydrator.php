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
use Plasticode\Hydrators\Interfaces\HydratorInterface;
use Plasticode\Models\DbModel;
use Plasticode\ObjectProxy;

class AssociationHydrator implements HydratorInterface
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
    public function hydrate(DbModel $entity) : Association
    {
        return $entity
            ->withFirstWord(
                $this->wordRepository->get($entity->firstWordId)
            )
            ->withSecondWord(
                $this->wordRepository->get($entity->secondWordId)
            )
            ->withFeedbacks(
                $this->associationFeedbackRepository->getAllByAssociation($entity)
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
            ->withMe(
                new ObjectProxy(
                    fn () => $this->auth->getUser()
                )
            )
            ->withCreator(
                $this->userRepository->get($entity->createdBy)
            );
    }
}
