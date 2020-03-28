<?php

namespace App\Repositories;

use App\Models\Association;
use App\Models\Game;
use App\Models\Language;
use App\Models\Turn;
use App\Models\User;
use App\Models\Word;
use App\Repositories\Interfaces\AssociationRepositoryInterface;
use App\Repositories\Interfaces\GameRepositoryInterface;
use App\Repositories\Interfaces\TurnRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\Interfaces\WordRepositoryInterface;
use App\Repositories\Traits\WithLanguageRepository;
use Plasticode\Collection;
use Plasticode\Data\Db;
use Plasticode\Models\DbModel;
use Plasticode\Query;
use Plasticode\Repositories\Idiorm\Basic\IdiormRepository;

class TurnRepository extends IdiormRepository implements TurnRepositoryInterface
{
    use WithLanguageRepository;

    protected string $entityClass = Turn::class;

    protected $sortField = 'id';
    protected $sortReverse = true;

    private GameRepositoryInterface $gameRepository;
    private WordRepositoryInterface $wordRepository;
    private UserRepositoryInterface $userRepository;
    private AssociationRepositoryInterface $associationRepository;

    public function __construct(
        Db $db,
        GameRepositoryInterface $gameRepository,
        WordRepositoryInterface $wordRepository,
        UserRepositoryInterface $userRepository,
        AssociationRepositoryInterface $associationRepository
    )
    {
        parent::__construct($db);

        $this->gameRepository = $gameRepository;
        $this->wordRepository = $wordRepository;
        $this->userRepository = $userRepository;
        $this->associationRepository = $associationRepository;
    }

    /**
     * @param Turn $entity
     */
    protected function hydrate(DbModel $entity) : Turn
    {
        return $entity
            ->withGame(
                $this->gameRepository->get($entity->gameId)
            )
            ->withWord(
                $this->wordRepository->get($entity->wordId)
            )
            ->withUser(
                $this->userRepository->get($entity->userId)
            )
            ->withAssociation(
                $this->associationRepository->get($entity->associationId)
            )
            ->withPrev(
                $this->get($entity->prevTurnId)
            );
    }

    public function get(?int $id) : ?Turn
    {
        return $this->getEntity($id);
    }

    public function getAllByGame(Game $game) : Collection
    {
        return $this
            ->query()
            ->where('game_id', $game->getId())
            ->all();
    }

    public function getAllByAssociation(Association $association) : Collection
    {
        return $this
            ->query()
            ->where('association_id', $association->getId())
            ->all();
    }

    protected function filterByUser(Query $query, User $user) : Query
    {
        return $query
            ->where('user_id', $user->getId());
    }

    public function getAllByUser(
        User $user,
        Language $language = null) : Collection
    {
        $query = $this->getByLanguageQuery($language);

        return $this
            ->filterByUser($query, $user)
            ->all();
    }

    public function getAllByWord(Word $word) : Collection
    {
        return $this
            ->query()
            ->where('word_id', $word->getId())
            ->all();
    }
}
