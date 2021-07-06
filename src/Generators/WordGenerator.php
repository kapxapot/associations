<?php

namespace App\Generators;

use App\Models\Word;
use App\Repositories\Interfaces\WordRepositoryInterface;
use Plasticode\Generators\Core\GeneratorContext;
use Plasticode\Generators\Generic\ChangingEntityGenerator;

class WordGenerator extends ChangingEntityGenerator
{
    private WordRepositoryInterface $wordRepository;

    public function __construct(
        GeneratorContext $context,
        WordRepositoryInterface $wordRepository
    )
    {
        parent::__construct($context);

        $this->wordRepository = $wordRepository;
    }

    protected function entityClass(): string
    {
        return Word::class;
    }

    public function getRepository(): WordRepositoryInterface
    {
        return $this->wordRepository;
    }
}
