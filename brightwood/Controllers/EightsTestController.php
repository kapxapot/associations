<?php

namespace Brightwood\Controllers;

use App\Bots\Factories\MessageRendererFactory;
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

        $game = new EightsGame(
            // todo: should be provided by container definitions (a factory!)
            new StoryParser(
                new MessageRendererFactory()
            ),
            new Cases(),
            $players
        );

        $game->withObserver($players->random());
        $game->withPlayersLine();

        $result = (MessageCollection::collect($game->start()))
            ->concat($game->run())
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
        $tgUserRepo = new TelegramUserRepositoryMock(
            new TelegramUserSeeder()
        );

        $tgUser = $tgUserRepo->get(1);

        $data = new EightsData();

        $data->withPlayerCount(4);
        $data->initGame($tgUser);

        $data->game()->start();
        $data->game()->run();

        return Response::text($response, json_encode($data));
    }
}
