<?php

namespace App\Generators;

use App\Models\WordRelation;
use App\Repositories\Interfaces\WordRelationRepositoryInterface;
use Plasticode\Generators\Core\GeneratorContext;
use Plasticode\Generators\Generic\ChangingEntityGenerator;

/**
 * This generator is for admin interface.
 */
class WordRelationAdminGenerator extends ChangingEntityGenerator
{
    private WordRelationRepositoryInterface $wordRelationRepository;

    public function __construct(
        GeneratorContext $context,
        WordRelationRepositoryInterface $wordRelationRepository
    )
    {
        parent::__construct($context);

        $this->wordRelationRepository = $wordRelationRepository;
    }

    protected function entityClass(): string
    {
        return WordRelation::class;
    }

    public function getRepository(): WordRelationRepositoryInterface
    {
        return $this->wordRelationRepository;
    }

    protected function getSettingsAlias(): string
    {
        return 'word_relations_admin';
    }

    public function getAdminParams(array $args): array
    {
        $params = parent::getAdminParams($args);

        $params['source'] = 'word_relations';

        return $params;
    }
}
