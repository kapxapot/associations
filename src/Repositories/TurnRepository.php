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
use App\Repositories\Traits\WithLanguageRepository;
use Plasticode\Query;
use Plasticode\Repositories\Idiorm\Basic\IdiormRepository;

class TurnRepository extends IdiormRepository implements TurnRepositoryInterface
{
    use WithLanguageRepository;

    protected string $entityClass = Turn::class;

    protected string $sortField = 'id';
    protected bool $sortReverse = true;

    public function get(?int $id) : ?Turn
    {
        return $this->getEntity($id);
    }

    public function getAllByGame(Game $game) : TurnCollection
    {
        return TurnCollection::from(
            $this
                ->query()
                ->where('game_id', $game->getId())
        );
    }

    public function getAllByAssociation(Association $association) : TurnCollection
    {
        return TurnCollection::from(
            $this
                ->query()
                ->where('association_id', $association->getId())
        );
    }

    protected function filterByUser(Query $query, User $user) : Query
    {
        return $query->where('user_id', $user->getId());
    }

    public function getAllByUser(
        User $user,
        Language $language = null
    ) : TurnCollection
    {
        $query = $this->getByLanguageQuery($language);

        return TurnCollection::from(
            $this->filterByUser($query, $user)
        );
    }

    public function getAllByWord(Word $word) : TurnCollection
    {
        return TurnCollection::from(
            $this
                ->query()
                ->where('word_id', $word->getId())
        );
    }
}
