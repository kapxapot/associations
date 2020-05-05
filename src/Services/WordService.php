<?php

namespace App\Services;

use App\Collections\WordCollection;
use App\Config\Interfaces\WordConfigInterface;
use App\Models\Language;
use App\Models\Turn;
use App\Models\User;
use App\Models\Word;
use App\Repositories\Interfaces\TurnRepositoryInterface;
use App\Repositories\Interfaces\WordRepositoryInterface;
use Plasticode\Exceptions\InvalidOperationException;
use Plasticode\Exceptions\InvalidResultException;
use Plasticode\Util\Strings;
use Plasticode\Validation\Interfaces\ValidatorInterface;
use Plasticode\Validation\ValidationRules;
use Respect\Validation\Validator;
use Webmozart\Assert\Assert;

class WordService
{
    private TurnRepositoryInterface $turnRepository;
    private WordRepositoryInterface $wordRepository;

    private CasesService $casesService;
    private ValidatorInterface $validator;
    private ValidationRules $validationRules;

    private WordConfigInterface $config;

    public function __construct(
        TurnRepositoryInterface $turnRepository,
        WordRepositoryInterface $wordRepository,
        CasesService $casesService,
        ValidatorInterface $validator,
        ValidationRules $validationRules,
        WordConfigInterface $config
    )
    {
        $this->turnRepository = $turnRepository;
        $this->wordRepository = $wordRepository;

        $this->casesService = $casesService;
        $this->validator = $validator;
        $this->validationRules = $validationRules;

        $this->config = $config;
    }

    /**
     * Normalized word string expected.
     */
    public function getOrCreate(
        Language $language,
        string $wordStr,
        User $user
    ) : Word
    {
        $word =
            $this->wordRepository->findInLanguage(
                $language,
                $wordStr
            )
            ?? $this->create($language, $wordStr, $user);

        if (is_null($word)) {
            throw new InvalidResultException(
                'Word can\'t be found or added.'
            );
        }
    
        return $word;
    }

    public function normalize(string $word) : string
    {
        return Strings::normalize($word);
    }

    /**
     * Creates new word
     * 
     * Word should be normalized in advance!
     * 
     * !!!!!!!!!!!!!!!!!!!
     * Same problem as with duplicate association
     * Two users can add the same word in parallel
     * !!!!!!!!!!!!!!!!!!!
     */
    public function create(Language $language, string $wordStr, User $user) : Word
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

        return $this
            ->wordRepository
            ->store(
                [
                    'language_id' => $language->getId(),
                    'word' => $wordStr,
                    'word_bin' => $wordStr,
                    'created_by' => $user->getId(),
                ]
            );
    }

    /**
     * Returns validation rules chain for word.
     */
    public function getRule() : Validator
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

    public function validateWord(string $wordStr) : void
    {
        $this
            ->validator
            ->validateArray(
                ['word' => $wordStr],
                ['word' => $this->getRule()]
            )
            ->throwOnFail();
    }

    public function approvedInvisibleAssociationsStr(Word $word) : ?string
    {
        $count = $word->approvedInvisibleAssociations()->count();

        return $this
            ->casesService
            ->invisibleAssociationCountStr($count);
    }

    public function notApprovedInvisibleAssociationsStr(Word $word) : ?string
    {
        $count = $word->notApprovedInvisibleAssociations()->count();

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
    ) : WordCollection
    {
        return
            WordCollection::from(
                $this
                    ->turnRepository
                    ->getAllByUser($user, $language)
                    ->map(
                        fn (Turn $t) => $t->word()
                    )
            )
            ->distinct();
    }
}
