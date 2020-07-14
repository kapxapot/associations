<?php

namespace App\Generators;

use App\Core\Serializer;
use App\Models\Turn;
use App\Repositories\Interfaces\GameRepositoryInterface;
use Plasticode\Generators\EntityGenerator;
use Psr\Container\ContainerInterface;

class GamesGenerator extends EntityGenerator
{
    private GameRepositoryInterface $gameRepository;
    private Serializer $serializer;

    public function __construct(ContainerInterface $container, string $entity)
    {
        parent::__construct($container, $entity);

        $this->gameRepository = $container->gameRepository;
        $this->serializer = $container->serializer;
    }

    public function afterLoad(array $item) : array
    {
        $item = parent::afterLoad($item);

        $id = $item[$this->idField];

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
