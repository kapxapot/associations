<?php

namespace App\Services;

use App\Config\Interfaces\WordConfigInterface;
use App\Models\Language;
use App\Models\User;
use App\Models\Word;
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
    /** @var WordConfigInterface */
    private $config;

    /** @var ValidatorInterface */
    private $validator;

    /** @var ValidationRules */
    private $validationRules;

    /** @var WordRepositoryInterface */
    private $wordRepository;

    public function __construct(
        WordConfigInterface $config,
        ValidatorInterface $validator,
        ValidationRules $validationRules,
        WordRepositoryInterface $wordRepository
    )
    {
        $this->config = $config;
        $this->validator = $validator;
        $this->validationRules = $validationRules;
        $this->wordRepository = $wordRepository;
    }

    /**
     * Normalized word string expected
     */
    public function getOrCreate(Language $language, string $wordStr, User $user) : Word
    {
        $word =
            Word::findInLanguage($language, $wordStr)
            ??
            $this->create($language, $wordStr, $user);

        if (is_null($word)) {
            throw new InvalidResultException('Word can\'t be found or added.');
        }
    
        return $word;
    }

    public function normalize($word) : string
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

        if (Word::findInLanguage($language, $wordStr) !== null) {
            throw new InvalidOperationException('Word already exists.');
        }
        
        $word = Word::create();
        
        $word->languageId = $language->getId();
        $word->word = $wordStr;
        $word->wordBin = $wordStr;
        $word->createdBy = $user->getId();

        return $this->wordRepository->save($word);
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
}
