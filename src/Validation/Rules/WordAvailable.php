<?php

namespace App\Validation\Rules;

use App\Models\Language;
use App\Repositories\Interfaces\WordRepositoryInterface;
use Respect\Validation\Rules\AbstractRule;

class WordAvailable extends AbstractRule
{
    private WordRepositoryInterface $wordRepository;
    private Language $language;
    private ?int $exceptId;

    public function __construct(
        WordRepositoryInterface $wordRepository,
        Language $language,
        ?int $exceptId = null
    )
    {
        $this->wordRepository = $wordRepository;
        $this->language = $language;
        $this->exceptId = $exceptId;
    }

    /**
     * @param string $input
     */
    public function validate($input)
    {
        $word = $this->wordRepository->findInLanguage(
            $this->language,
            $input,
            $this->exceptId
        );

        return $word === null;
    }
}
