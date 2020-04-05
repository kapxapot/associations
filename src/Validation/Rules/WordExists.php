<?php

namespace App\Validation\Rules;

use App\Repositories\Interfaces\WordRepositoryInterface;
use Respect\Validation\Rules\AbstractRule;

class WordExists extends AbstractRule
{
    private WordRepositoryInterface $wordRepository;

    public function __construct(
        WordRepositoryInterface $wordRepository
    )
    {
        $this->wordRepository = $wordRepository;
    }

    public function validate($input)
    {
        $word = $this->wordRepository->get($input);
        
        return $word !== null;
    }
}
