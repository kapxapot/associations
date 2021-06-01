<?php

namespace App\Generators;

use App\Models\WordRelationType;
use App\Repositories\Interfaces\WordRelationTypeRepositoryInterface;
use Plasticode\Core\Interfaces\TranslatorInterface;
use Plasticode\Generators\Core\GeneratorContext;
use Plasticode\Generators\Generic\EntityGenerator;

class WordRelationTypeGenerator extends EntityGenerator
{
    private WordRelationTypeRepositoryInterface $wordRelationTypeRepository;

    private TranslatorInterface $translator;

    public function __construct(
        GeneratorContext $context,
        WordRelationTypeRepositoryInterface $wordRelationTypeRepository,
        TranslatorInterface $translator
    )
    {
        parent::__construct($context);

        $this->wordRelationTypeRepository = $wordRelationTypeRepository;

        $this->translator = $translator;
    }

    protected function entityClass(): string
    {
        return WordRelationType::class;
    }

    public function getRepository(): WordRelationTypeRepositoryInterface
    {
        return $this->wordRelationTypeRepository;
    }

    public function afterLoad(array $item): array
    {
        $item = parent::afterLoad($item);

        $id = $item[$this->idField()];

        $type = $this->wordRelationTypeRepository->get($id);

        if ($type) {
            $item['localized_name'] = $this->translator->translate($type->name);
        }

        return $item;
    }
}
