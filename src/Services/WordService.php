<?php

namespace App\Services;

use App\Collections\WordCollection;
use App\Config\Interfaces\WordConfigInterface;
use App\Events\Word\WordCreatedEvent;
use App\Models\Language;
use App\Models\User;
use App\Models\Word;
use App\Parsing\DefinitionParser;
use App\Repositories\Interfaces\TurnRepositoryInterface;
use App\Repositories\Interfaces\WordRepositoryInterface;
use App\Semantics\Definition\DefinitionAggregate;
use Exception;
use Plasticode\Events\EventDispatcher;
use Plasticode\Exceptions\InvalidOperationException;
use Plasticode\Exceptions\InvalidResultException;
use Plasticode\Exceptions\ValidationException;
use Plasticode\Search\SearchParams;
use Plasticode\Search\SearchResult;
use Plasticode\Util\Strings;
use Plasticode\Validation\Interfaces\ValidatorInterface;
use Plasticode\Validation\ValidationRules;
use Respect\Validation\Validator;
use Webmozart\Assert\Assert;

/**
 * @emits WordCreatedEvent
 */
class WordService
{
    private TurnRepositoryInterface $turnRepository;
    private WordRepositoryInterface $wordRepository;

    private CasesService $casesService;
    private ValidatorInterface $validator;
    private ValidationRules $validationRules;

    private WordConfigInterface $config;

    private EventDispatcher $eventDispatcher;

    private DefinitionParser $definitionParser;

    public function __construct(
        TurnRepositoryInterface $turnRepository,
        WordRepositoryInterface $wordRepository,
        CasesService $casesService,
        ValidatorInterface $validator,
        ValidationRules $validationRules,
        WordConfigInterface $config,
        EventDispatcher $eventDispatcher,
        DefinitionParser $definitionParser
    )
    {
        $this->turnRepository = $turnRepository;
        $this->wordRepository = $wordRepository;

        $this->casesService = $casesService;
        $this->validator = $validator;
        $this->validationRules = $validationRules;

        $this->config = $config;

        $this->eventDispatcher = $eventDispatcher;

        $this->definitionParser = $definitionParser;
    }

    /**
     * Normalized word string expected.
     */
    public function getOrCreate(
        User $user,
        Language $language,
        string $wordStr,
        ?string $originalUtterance = null
    ): Word
    {
        $word = $this->wordRepository->findInLanguage($language, $wordStr)
            ?? $this->create($user, $language, $wordStr, $originalUtterance);

        if ($word === null) {
            throw new InvalidResultException(
                'Word can\'t be found or added.'
            );
        }

        return $this->swapWord($word);
    }

    /**
     * Swaps the word with another one according to the following logic:
     *
     * - If the word is disabled and has a canonical word, use the canonical one.
     *
     * Todo: Should be extracted to some external strategy.
     */
    private function swapWord(Word $word): Word
    {
        if ($word->isFuzzyDisabled() && $word->hasMain()) {
            return $word->canonical();
        }

        return $word;
    }

    public function normalize(?string $word): ?string
    {
        return Strings::normalize($word);
    }

    /**
     * Creates a new word.
     *
     * Word must be normalized in advance!
     *
     * !!!!!!!!!!!!!!!!!!!
     * Same problem as with duplicate association
     * Two users can add the same word in parallel
     * !!!!!!!!!!!!!!!!!!!
     */
    public function create(
        User $user,
        Language $language,
        string $wordStr,
        ?string $originalUtterance = null
    ): Word
    {
        Assert::notNull($language, 'Language must be non-null.');
        Assert::notEmpty($wordStr, 'Word can\'t be empty.');
        Assert::notNull($user, 'User must be non-null.');

        $word = $this->wordRepository->findInLanguage(
            $language,
            $wordStr
        );

        if ($word) {
            throw new InvalidOperationException('Word already exists.');
        }

        $word = $this
            ->wordRepository
            ->store(
                [
                    'language_id' => $language->getId(),
                    'word' => $wordStr,
                    'original_utterance' => $originalUtterance,
                    'created_by' => $user->getId(),
                ]
            );

        $this->eventDispatcher->dispatch(
            new WordCreatedEvent($word)
        );

        return $word;
    }

    /**
     * Returns validation rules chain for word.
     */
    public function getRule(): Validator
    {
        return $this
            ->validationRules
            ->get('text')
            ->length(
                $this->config->wordMinLength(),
                $this->config->wordMaxLength()
            )
            ->wordIsValid();
    }

    /**
     * Returns `true` if the word is valid.
     */
    public function isWordValid(?string $wordStr): bool
    {
        $valid = true;

        try {
            $this->validateWord($wordStr);
        } catch (Exception $ex) {
            $valid = false;
        }

        return $valid;
    }

    /**
     * Throws exception if the word is not valid.
     * 
     * @throws ValidationException
     */
    public function validateWord(?string $wordStr): void
    {
        $this
            ->validator
            ->validateArray(
                ['word' => $wordStr],
                ['word' => $this->getRule()]
            )
            ->throwOnFail();
    }

    public function approvedInvisibleAssociationsStr(Word $word): ?string
    {
        $count = $word->approvedInvisibleAssociations()->count();

        return $this
            ->casesService
            ->invisibleAssociationCountStr($count);
    }

    public function notApprovedInvisibleAssociationsStr(Word $word): ?string
    {
        $count = $word->notApprovedInvisibleAssociations()->count();

        return $this
            ->casesService
            ->invisibleAssociationCountStr($count);
    }

    public function disabledInvisibleAssociationsStr(Word $word): ?string
    {
        $count = $word->disabledInvisibleAssociations()->count();

        return $this
            ->casesService
            ->invisibleAssociationCountStr($count);
    }

    /**
     * Returns all words of specified language used by the user.
     */
    public function getAllUsedBy(
        User $user,
        Language $language = null
    ): WordCollection
    {
        return $this
            ->turnRepository
            ->getAllByUser($user, $language)
            ->words();
    }

    public function searchAllNotMature(
        SearchParams $searchParams,
        ?Language $language = null
    ): SearchResult
    {
        $data = $this
            ->wordRepository
            ->searchAllNotMature($searchParams, $language);

        $totalCount = $this->wordRepository->getNotMatureCount($language);

        $filteredCount = $searchParams->hasFilter()
            ? $this->wordRepository->getNotMatureCount($language, $searchParams->filter())
            : $totalCount;

        return new SearchResult($data, $totalCount, $filteredCount);
    }

    /**
     * Returns word only in case it is not null and the word is visible for the user.
     */
    public function purgeFor(?Word $word, ?User $user): ?Word
    {
        return ($word && $word->isVisibleFor($user))
            ? $word
            : null;
    }

    public function getParsedDefinition(Word $word): ?DefinitionAggregate
    {
        return $word->definition()
            ? $this->definitionParser->parse($word->definition())
            : null;
    }
}
