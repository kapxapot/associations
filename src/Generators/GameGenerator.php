<?php

namespace App\Generators;

use App\Models\Game;
use App\Repositories\Interfaces\GameRepositoryInterface;
use Plasticode\Generators\Core\GeneratorContext;
use Plasticode\Generators\Generic\EntityGenerator;

class GameGenerator extends EntityGenerator
{
    private GameRepositoryInterface $gameRepository;

    public function __construct(
        GeneratorContext $context,
        GameRepositoryInterface $gameRepository
    )
    {
        parent::__construct($context);

        $this->gameRepository = $gameRepository;
    }

    protected function entityClass(): string
    {
        return Game::class;
    }

    public function getRepository(): GameRepositoryInterface
    {
        return $this->gameRepository;
    }

    public function afterLoad(array $item): array
    {
        $item = parent::afterLoad($item);

        $id = $item[$this->idField()];

        $game = $this->gameRepository->get($id);

        if ($game) {
            $item['language'] = $game->language()->name;
            $item['user_name'] = $game->user()->displayName();
            $item['url'] = $game->url();
        }

        return $item;
    }
}
