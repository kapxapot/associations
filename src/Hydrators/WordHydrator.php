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
use App\Repositories\Interfaces\WordOverrideRepositoryInterface;
use App\Repositories\Interfaces\WordRelationRepositoryInterface;
use App\Repositories\Interfaces\WordRepositoryInterface;
use App\Semantics\Interfaces\AssociationAggregatorInterface;
use App\Services\DictionaryService;
use App\Services\WordService;
use Plasticode\Hydrators\Generic\Hydrator;
use Plasticode\Models\Generic\DbModel;

class WordHydrator extends Hydrator
{
    private AssociationRepositoryInterface $associationRepository;
    private LanguageRepositoryInterface $languageRepository;
    private TurnRepositoryInterface $turnRepository;
    private UserRepositoryInterface $userRepository;
    private WordRepositoryInterface $wordRepository;
    private WordFeedbackRepositoryInterface $wordFeedbackRepository;
    private WordOverrideRepositoryInterface $wordOverrideRepository;
    private WordRelationRepositoryInterface $wordRelationRepository;

    private AuthInterface $auth;
    private LinkerInterface $linker;

    private DictionaryService $dictionaryService;
    private WordService $wordService;

    private AssociationAggregatorInterface $associationAggregator;

    public function __construct(
        AssociationRepositoryInterface $associationRepository,
        LanguageRepositoryInterface $languageRepository,
        TurnRepositoryInterface $turnRepository,
        UserRepositoryInterface $userRepository,
        WordRepositoryInterface $wordRepository,
        WordFeedbackRepositoryInterface $wordFeedbackRepository,
        WordOverrideRepositoryInterface $wordOverrideRepository,
        WordRelationRepositoryInterface $wordRelationRepository,
        AuthInterface $auth,
        LinkerInterface $linker,
        DictionaryService $dictionaryService,
        WordService $wordService,
        AssociationAggregatorInterface $associationAggregator
    )
    {
        $this->associationRepository = $associationRepository;
        $this->languageRepository = $languageRepository;
        $this->turnRepository = $turnRepository;
        $this->userRepository = $userRepository;
        $this->wordRepository = $wordRepository;
        $this->wordFeedbackRepository = $wordFeedbackRepository;
        $this->wordOverrideRepository = $wordOverrideRepository;
        $this->wordRelationRepository = $wordRelationRepository;

        $this->auth = $auth;
        $this->linker = $linker;

        $this->dictionaryService = $dictionaryService;
        $this->wordService = $wordService;

        $this->associationAggregator = $associationAggregator;
    }

    /**
     * @param Word $entity
     */
    public function hydrate(DbModel $entity): Word
    {
        return $entity
            ->withAggregatedAssociations(
                fn () => $this->associationAggregator->aggregateFor($entity)
            )
            ->withAssociations(
                fn () => $this->associationRepository->getAllByWord($entity)
            )
            ->withDefinition(
                fn () => $this->wordService->getDefinition($entity)
            )
            ->withParsedDefinition(
                fn () => $this->wordService->getParsedDefinition($entity)
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
            ->withMain(
                fn () => $this->wordRepository->get($entity->mainId)
            )
            ->withDependents(
                fn () => $this->wordRepository->getAllByMain($entity)
            )
            ->withOverrides(
                fn () => $this->wordOverrideRepository->getAllByWord($entity)
            )
            ->withRelations(
                fn () => $this->wordRelationRepository->getAllByWord($entity)
            )
            ->withCounterRelations(
                fn () => $this->wordRelationRepository->getAllByMainWord($entity)
            )
            ->withCreator(
                fn () => $this->userRepository->get($entity->createdBy)
            );
    }
}
