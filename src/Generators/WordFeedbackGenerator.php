<?php

namespace App\Generators;

use App\Models\WordFeedback;
use App\Repositories\Interfaces\WordFeedbackRepositoryInterface;
use Plasticode\Generators\Core\GeneratorContext;
use Plasticode\Generators\Generic\ChangingEntityGenerator;

class WordFeedbackGenerator extends ChangingEntityGenerator
{
    private WordFeedbackRepositoryInterface $wordFeedbackRepository;

    public function __construct(
        GeneratorContext $context,
        WordFeedbackRepositoryInterface $wordFeedbackRepository
    )
    {
        parent::__construct($context);

        $this->wordFeedbackRepository = $wordFeedbackRepository;
    }

    protected function entityClass(): string
    {
        return WordFeedback::class;
    }

    public function getRepository(): WordFeedbackRepositoryInterface
    {
        return $this->wordFeedbackRepository;
    }
}
