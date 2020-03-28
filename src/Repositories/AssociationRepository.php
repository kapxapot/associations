<?php

namespace App\Repositories;

use App\Models\Association;
use App\Models\Word;
use App\Repositories\Interfaces\AssociationRepositoryInterface;
use App\Repositories\Interfaces\WordRepositoryInterface;
use Plasticode\Collection;
use Plasticode\Data\Db;
use Plasticode\Models\DbModel;

class AssociationRepository extends LanguageElementRepository implements AssociationRepositoryInterface
{
    protected string $entityClass = Association::class;

    private WordRepositoryInterface $wordRepository;

    public function __construct(
        Db $db,
        WordRepositoryInterface $wordRepository
    )
    {
        parent::__construct($db);

        $this->wordRepository = $wordRepository;
    }

    /**
     * @param Association $entity
     */
    protected function hydrate(DbModel $entity) : Association
    {
        return $entity
            ->withFirstWord(
                $this->wordRepository->get($entity->firstWordId)
            )
            ->withSecondWord(
                $this->wordRepository->get($entity->secondWordId)
            );
    }

    public function getAllByWord(Word $word) : Collection
    {
        return $this
            ->query()
            ->whereAnyIs(
                [
                    ['first_word_id' => $word->getId()],
                    ['second_word_id' => $word->getId()],
                ]
            )
            ->all();
    }

    public function getByPair(Word $first, Word $second) : ?Association
    {
        return $this
            ->query()
            ->where('first_word_id', $first->getId())
            ->where('second_word_id', $second->getId())
            ->one();
    }
}
