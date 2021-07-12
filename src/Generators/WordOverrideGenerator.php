<?php

namespace App\Generators;

use App\Models\WordOverride;
use App\Repositories\Interfaces\WordOverrideRepositoryInterface;
use App\Semantics\PartOfSpeech;
use Plasticode\Core\Interfaces\TranslatorInterface;
use Plasticode\Generators\Core\GeneratorContext;
use Plasticode\Generators\Generic\EntityGenerator;
use Plasticode\Search\SearchResult;

class WordOverrideGenerator extends EntityGenerator
{
    private WordOverrideRepositoryInterface $wordOverrideRepository;
    private TranslatorInterface $translator;

    public function __construct(
        GeneratorContext $context,
        WordOverrideRepositoryInterface $wordOverrideRepository,
        TranslatorInterface $translator
    )
    {
        parent::__construct($context);

        $this->wordOverrideRepository = $wordOverrideRepository;
        $this->translator = $translator;
    }

    protected function entityClass(): string
    {
        return WordOverride::class;
    }

    public function getRepository(): WordOverrideRepositoryInterface
    {
        return $this->wordOverrideRepository;
    }

    public function afterLoad(array $item): array
    {
        $item = parent::afterLoad($item);

        $id = $item[$this->idField()];

        $override = $this->wordOverrideRepository->get($id);

        if ($override) {
            $word = $override->word();

            $item['word'] = [
                'original_word' => $word->originalWord,
                'name' => $word->word,
                'url' => $word->url(),
            ];

            $item['parts_of_speech'] = $word->partsOfSpeechOverride()
                ? $word->partsOfSpeechOverride()
                    ->map(
                        fn (PartOfSpeech $p) => $this->translator->translate($p->shortName())
                    )
                    ->join(', ')
                : null;
        }

        return $item;
    }
}
