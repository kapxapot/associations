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

    protected function getRepository(): WordRepositoryInterface
    {
        return $this->wordRepository;
    }

    public function afterLoad(array $item): array
    {
        $item = parent::afterLoad($item);

        $id = $item[$this->idField()];

        $word = $this->wordRepository->get($id);

        if ($word) {
            $item['name'] = $word->word;
            $item['url'] = $word->url();
            $item['language'] = $word->language()->name;

            $dw = $word->dictWord();
            $item['has_dict_word'] = $dw && $dw->isValid();

            $def = $word->definition();
            $item['has_definition'] = $def && $def->isValid();
        }

        return $item;
    }
}
