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

    public function afterLoad(array $item): array
    {
        $item = parent::afterLoad($item);

        $id = $item[$this->idField()];

        $wordFeedback = $this->wordFeedbackRepository->get($id);

        if ($wordFeedback) {
            $word = $wordFeedback->word();

            $item['word'] = [
                'name' => $word->word,
                'url' => $word->url(),
            ];

            $duplicate = $wordFeedback->duplicate();

            $item['duplicate'] = $duplicate
                ? [
                    'name' => $duplicate->word,
                    'url' => $duplicate->url(),
                ]
                : null;
        }

        return $item;
    }
}
