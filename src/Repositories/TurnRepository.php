<?php

namespace App\Repositories;

use App\Collections\TurnCollection;
use App\Models\Association;
use App\Models\Game;
use App\Models\Language;
use App\Models\Turn;
use App\Models\User;
use App\Models\Word;
use App\Repositories\Interfaces\TurnRepositoryInterface;
use App\Repositories\Traits\ByUserRepository;
use App\Repositories\Traits\WithLanguageRepository;
use App\Repositories\Traits\WithWordRepository;
use Plasticode\Repositories\Idiorm\Generic\IdiormRepository;

class TurnRepository extends IdiormRepository implements TurnRepositoryInterface
{
    use ByUserRepository;
    use WithLanguageRepository;
    use WithWordRepository;

    protected string $sortField = 'id';
    protected bool $sortReverse = true;

    protected function entityClass(): string
    {
        return Turn::class;
    }

    public function get(?int $id): ?Turn
    {
        return $this->getEntity($id);
    }

    public function save(Turn $turn): Turn
    {
        return $this->saveEntity($turn);
    }

    public function getAllByGame(Game $game): TurnCollection
    {
        return TurnCollection::from(
            $this
                ->query()
                ->where('game_id', $game->getId())
        );
    }

    public function getAllByAssociation(Association $association): TurnCollection
    {
        return TurnCollection::from(
            $this
                ->query()
                ->where('association_id', $association->getId())
        );
    }

    public function getAllByLanguage(Language $language): TurnCollection
    {
        return TurnCollection::from(
            $this->getByLanguageQuery($language)
        );
    }

    public function getAllByUser(
        User $user,
        ?Language $language = null
    ): TurnCollection
    {
        $query = $this->getByLanguageQuery($language);

        return TurnCollection::from(
            $this->filterByUser($query, $user)
        );
    }

    public function getAllByWord(Word $word): TurnCollection
    {
        return TurnCollection::from(
            $this->byWordQuery($word)
        );
    }
}
