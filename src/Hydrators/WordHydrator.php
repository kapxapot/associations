<?php

namespace App\Hydrators;

use App\Auth\Interfaces\AuthInterface;
use App\Collections\AssociationCollection;
use App\Core\Interfaces\LinkerInterface;
use App\Models\AggregatedAssociation;
use App\Models\Association;
use App\Models\Word;
use App\Repositories\Interfaces\AssociationRepositoryInterface;
use App\Repositories\Interfaces\LanguageRepositoryInterface;
use App\Repositories\Interfaces\TurnRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\Interfaces\WordFeedbackRepositoryInterface;
use App\Repositories\Interfaces\WordOverrideRepositoryInterface;
use App\Repositories\Interfaces\WordRelationRepositoryInterface;
use App\Repositories\Interfaces\WordRepositoryInterface;
use App\Services\AssociationService;
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

    private AssociationService $associationService;
    private DictionaryService $dictionaryService;
    private WordService $wordService;

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
        AssociationService $associationService,
        DictionaryService $dictionaryService,
        WordService $wordService
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

        $this->associationService = $associationService;
        $this->dictionaryService = $dictionaryService;
        $this->wordService = $wordService;
    }

    /**
     * @param Word $entity
     */
    public function hydrate(DbModel $entity): Word
    {
        return $entity
            ->withAggregatedAssociations(
                $this->frozen(
                    function () use ($entity) {
                        $aggregatedAssociations = $this
                            ->associationService
                            ->getAggregatedAssociationsFor($entity);

                        // init associations so the are not loaded again
                        // this will word only if the aggregated associations
                        // are loaded first
                        $entity->withAssociations(
                            $aggregatedAssociations->distillByWord($entity)
                        );

                        return $aggregatedAssociations;
                    }
                )
            )
            ->withAssociations(
                $this->frozen(
                    fn () => $this->associationRepository->getAllByWord($entity)
                )
            )
            ->withDefinition(
                $this->frozen(
                    fn () => $this->wordService->getDefinition($entity)
                )
            )
            ->withParsedDefinition(
                fn () => $this->wordService->getParsedDefinition($entity)
            )
            ->withFeedbacks(
                $this->frozen(
                    fn () => $this->wordFeedbackRepository->getAllByWord($entity)
                )
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
                $this->frozen(
                    fn () => $this->dictionaryService->getByWord($entity)
                )
            )
            ->withMain(
                fn () => $this->wordRepository->get($entity->mainId)
            )
            ->withDependents(
                fn () => $this->wordRepository->getAllByMain($entity)
            )
            ->withOverrides(
                $this->frozen(
                    fn () => $this->wordOverrideRepository->getAllByWord($entity)
                )
            )
            ->withRelations(
                $this->frozen(
                    fn () => $this->wordRelationRepository->getAllByWord($entity)
                )
            )
            ->withCounterRelations(
                $this->frozen(
                    fn () => $this->wordRelationRepository->getAllByMainWord($entity)
                )
            )
            ->withCreator(
                fn () => $this->userRepository->get($entity->createdBy)
            );
    }
}
