<?php

namespace App\Hydrators;

use App\Models\Definition;
use App\Repositories\Interfaces\WordRepositoryInterface;
use Plasticode\Hydrators\Generic\Hydrator;
use Plasticode\Models\Generic\DbModel;

class DefinitionHydrator extends Hydrator
{
    private WordRepositoryInterface $wordRepository;

    public function __construct(
        WordRepositoryInterface $wordRepository
    )
    {
        $this->wordRepository = $wordRepository;
    }

    /**
     * @param Definition $entity
     */
    public function hydrate(DbModel $entity): Definition
    {
        return $entity
            ->withWord(
                fn () => $this->wordRepository->get($entity->wordId)
            );
    }
}
