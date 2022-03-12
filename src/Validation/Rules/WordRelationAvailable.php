<?php

namespace App\Validation\Rules;

use App\Repositories\Interfaces\WordRelationRepositoryInterface;
use App\Repositories\Interfaces\WordRelationTypeRepositoryInterface;
use App\Repositories\Interfaces\WordRepositoryInterface;
use App\Services\LanguageService;
use Respect\Validation\Rules\AbstractRule;

/**
 * Tries to find a word relation with the same word/type/main word, but not equal to the current word relation.
 */
class WordRelationAvailable extends AbstractRule
{
    private WordRepositoryInterface $wordRepository;
    private WordRelationRepositoryInterface $wordRelationRepository;
    private WordRelationTypeRepositoryInterface $wordRelationTypeRepository;

    private LanguageService $languageService;

    private int $typeId;
    private string $mainWordStr;
    private ?int $exceptId;

    public function __construct(
        WordRepositoryInterface $wordRepository,
        WordRelationRepositoryInterface $wordRelationRepository,
        WordRelationTypeRepositoryInterface $wordRelationTypeRepository,
        LanguageService $languageService,
        int $typeId,
        string $mainWordStr,
        ?int $exceptId = null
    )
    {
        $this->wordRepository = $wordRepository;
        $this->wordRelationRepository = $wordRelationRepository;
        $this->wordRelationTypeRepository = $wordRelationTypeRepository;

        $this->languageService = $languageService;

        $this->typeId = $typeId;
        $this->mainWordStr = $mainWordStr;
        $this->exceptId = $exceptId;
    }

    /**
     * @param string $input Word id.
     */
    public function validate($input)
    {
        $word = $this->wordRepository->get(intval($input));
        $type = $this->wordRelationTypeRepository->get(intval($this->typeId));

        if ($word === null || $type === null) {
            return true;
        }

        $mainWord = $this->languageService->findWord(
            $word->language(),
            $this->mainWordStr
        );

        if ($mainWord === null) {
            return true;
        }

        $wordRelation = $this->wordRelationRepository->find(
            $word,
            $type,
            $mainWord,
            $this->exceptId
        );

        return $wordRelation === null;
    }
}
