<?php

namespace Brightwood\Controllers;

use Brightwood\Collections\Cards\PlayerCollection;
use Brightwood\Models\Cards\Games\EightsGame;
use Brightwood\Models\Cards\Players\Bot;
use Brightwood\Models\Cards\Players\FemaleBot;
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

        $game = (new EightsGame(
            new StoryParser(),
            new Cases(),
            ...$players
        ))->withObserver($players->random());

        $result = $game
            ->run()
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
}
