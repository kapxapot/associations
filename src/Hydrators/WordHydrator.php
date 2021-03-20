<?php

namespace App\Hydrators;

use App\Auth\Interfaces\AuthInterface;
use App\Core\Interfaces\LinkerInterface;
use App\Models\Word;
use App\Repositories\Interfaces\AssociationRepositoryInterface;
use App\Repositories\Interfaces\DefinitionRepositoryInterface;
use App\Repositories\Interfaces\LanguageRepositoryInterface;
use App\Repositories\Interfaces\TurnRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\Interfaces\WordFeedbackRepositoryInterface;
use App\Repositories\Interfaces\WordOverrideRepositoryInterface;
use App\Services\DictionaryService;
use App\Services\WordService;
use Plasticode\Hydrators\Generic\Hydrator;
use Plasticode\Models\Generic\DbModel;

class WordHydrator extends Hydrator
{
    private AssociationRepositoryInterface $associationRepository;
    private DefinitionRepositoryInterface $definitionRepository;
    private LanguageRepositoryInterface $languageRepository;
    private TurnRepositoryInterface $turnRepository;
    private UserRepositoryInterface $userRepository;
    private WordFeedbackRepositoryInterface $wordFeedbackRepository;
    private WordOverrideRepositoryInterface $wordOverrideRepository;

    private AuthInterface $auth;
    private LinkerInterface $linker;

    private DictionaryService $dictionaryService;
    private WordService $wordService;

    public function __construct(
        AssociationRepositoryInterface $associationRepository,
        DefinitionRepositoryInterface $definitionRepository,
        LanguageRepositoryInterface $languageRepository,
        TurnRepositoryInterface $turnRepository,
        UserRepositoryInterface $userRepository,
        WordFeedbackRepositoryInterface $wordFeedbackRepository,
        WordOverrideRepositoryInterface $wordOverrideRepository,
        AuthInterface $auth,
        LinkerInterface $linker,
        DictionaryService $dictionaryService,
        WordService $wordService
    )
    {
        $this->associationRepository = $associationRepository;
        $this->definitionRepository = $definitionRepository;
        $this->languageRepository = $languageRepository;
        $this->turnRepository = $turnRepository;
        $this->userRepository = $userRepository;
        $this->wordFeedbackRepository = $wordFeedbackRepository;
        $this->wordOverrideRepository = $wordOverrideRepository;

        $this->auth = $auth;
        $this->linker = $linker;

        $this->dictionaryService = $dictionaryService;
        $this->wordService = $wordService;
    }

    /**
     * @param Word $entity
     */
    public function hydrate(DbModel $entity): Word
    {
        return $entity
            ->withAssociations(
                fn () => $this->associationRepository->getAllByWord($entity)
            )
            ->withDefinition(
                fn () => $this->definitionRepository->getByWord($entity)
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
            ->withOverrides(
                fn () => $this->wordOverrideRepository->getAllByWord($entity)
            )
            ->withCreator(
                fn () => $this->userRepository->get($entity->createdBy)
            );
    }
}
