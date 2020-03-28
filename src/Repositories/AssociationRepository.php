<?php

namespace App\Repositories;

use App\Core\Interfaces\LinkerInterface;
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
    private LinkerInterface $linker;

    public function __construct(
        Db $db,
        WordRepositoryInterface $wordRepository,
        LinkerInterface $linker
    )
    {
        parent::__construct($db);

        $this->wordRepository = $wordRepository;
        $this->linker = $linker;
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
            )
            ->withUrl(
                $this->linker->association($entity)
            )
            ->withTurns(
                Turn::getByAssociation($this);
            );
    }

    public function get(?int $id) : ?Association
    {
        return $this->getEntity($id);
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
