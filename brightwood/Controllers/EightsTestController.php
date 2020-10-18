<?php

namespace Brightwood\Controllers;

use App\Models\TelegramUser;
use App\Testing\Mocks\Repositories\TelegramUserRepositoryMock;
use App\Testing\Seeders\TelegramUserSeeder;
use Brightwood\Collections\Cards\PlayerCollection;
use Brightwood\Collections\MessageCollection;
use Brightwood\Models\Cards\Games\EightsGame;
use Brightwood\Models\Cards\Players\Bot;
use Brightwood\Models\Cards\Players\FemaleBot;
use Brightwood\Models\Data\EightsData;
use Brightwood\Models\Messages\Interfaces\MessageInterface;
use Brightwood\Parsing\StoryParser;
use Plasticode\Core\Response;
use Plasticode\Util\Cases;
use Plasticode\Util\Text;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class EightsTestController
{
    public function play(
        ServerRequestInterface $request,
        ResponseInterface $response
    )
    {
        $players = PlayerCollection::collect(
            new Bot('Кузьма'),
            new Bot('Миша'),
            new FemaleBot('Соня'),
            new FemaleBot('Аглая')
        );

        $game =new EightsGame(
            new StoryParser(),
            new Cases(),
            $players
        );

        $game->withObserver($players->random());
        $game->withPlayersLine();

        $result =
            (MessageCollection::collect(
                $game->start()
            ))
            ->concat(
                $game->run()
            )
            ->map(
                fn ($msg) => $this->wrap($msg)
            )
            ->join();

        return Response::text($response, $result);
    }

    private function wrap(MessageInterface $message) : string
    {
        return
            '<div style="background-color: #efefef; padding: 0.5rem;">' .
            Text::join($message->lines(), '<br /><br />') .
            '</div><br />';
    }
    
    public function serialize(
        ServerRequestInterface $request,
        ResponseInterface $response
    )
    {
        $repo = new TelegramUserRepositoryMock(
            new TelegramUserSeeder()
        );

        $data = new EightsData(
            $repo->get(1)
        );

        $data->setPlayerCount(4);

        $data->initGame();
        $data->game()->start();
        $data->game()->run();

        return Response::text($response, json_encode($data));
    }
}
