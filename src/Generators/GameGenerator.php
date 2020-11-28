<?php

namespace App\Generators;

use App\Core\Serializer;
use App\Models\Game;
use App\Models\Turn;
use App\Repositories\Interfaces\GameRepositoryInterface;
use Plasticode\Generators\EntityGenerator;
use Plasticode\Generators\GeneratorContext;

class GameGenerator extends EntityGenerator
{
    private GameRepositoryInterface $gameRepository;
    private Serializer $serializer;

    public function __construct(
        GeneratorContext $context,
        GameRepositoryInterface $gameRepository,
        Serializer $serializer
    )
    {
        parent::__construct($context);

        $this->gameRepository = $gameRepository;
        $this->serializer = $serializer;
    }

    protected function entityClass() : string
    {
        return Game::class;
    }

    public function afterLoad(array $item) : array
    {
        $item = parent::afterLoad($item);

        $id = $item[$this->idField()];

        $game = $this->gameRepository->get($id);

        if ($game) {
            $item['url'] = $game->url();

            $item['history'] = $game->turns()->reverse()->map(
                fn (Turn $t) => $this->serializer->serializeTurn($t)
            );
        }

        return $item;
    }
}
