<?php

namespace App\Hydrators;

use App\Auth\Interfaces\AuthInterface;
use App\Core\Interfaces\LinkerInterface;
use App\Models\Word;
use App\Repositories\Interfaces\AssociationRepositoryInterface;
use App\Repositories\Interfaces\LanguageRepositoryInterface;
use App\Repositories\Interfaces\TurnRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\Interfaces\WordFeedbackRepositoryInterface;
use App\Services\DictionaryService;
use Plasticode\Hydrators\Basic\Hydrator;
use Plasticode\Models\Basic\DbModel;

class WordHydrator extends Hydrator
{
    private AssociationRepositoryInterface $associationRepository;
    private LanguageRepositoryInterface $languageRepository;
    private TurnRepositoryInterface $turnRepository;
    private UserRepositoryInterface $userRepository;
    private WordFeedbackRepositoryInterface $wordFeedbackRepository;

    private AuthInterface $auth;
    private LinkerInterface $linker;

    private DictionaryService $dictionaryService;

    public function __construct(
        AssociationRepositoryInterface $associationRepository,
        LanguageRepositoryInterface $languageRepository,
        TurnRepositoryInterface $turnRepository,
        UserRepositoryInterface $userRepository,
        WordFeedbackRepositoryInterface $wordFeedbackRepository,
        AuthInterface $auth,
        LinkerInterface $linker,
        DictionaryService $dictionaryService
    )
    {
        $this->associationRepository = $associationRepository;
        $this->languageRepository = $languageRepository;
        $this->turnRepository = $turnRepository;
        $this->userRepository = $userRepository;
        $this->wordFeedbackRepository = $wordFeedbackRepository;

        $this->auth = $auth;
        $this->linker = $linker;

        $this->dictionaryService = $dictionaryService;
    }

    /**
     * @param Word $entity
     */
    public function hydrate(DbModel $entity) : Word
    {
        return $entity
            ->withAssociations(
                fn () => $this->associationRepository->getAllByWord($entity)
            )
            ->withFeedbacks(
                fn () => $this->wordFeedbackRepository->getAllByWord($entity)
            )
            ->withUrl(
                fn () => $this->linker->word($entity)
            )
            ->withLanguage(
                fn () => $this->languageRepository->get($entity->languageId)
            )
            ->withTurns(
                fn () => $this->turnRepository->getAllByWord($entity)
            )
            ->withMe(
                fn () => $this->auth->getUser()
            )
            ->withDictWord(
                fn () => $this->dictionaryService->getByWord($entity)
            )
            ->withCreator(
                fn () => $this->userRepository->get($entity->createdBy)
            );
    }
}
