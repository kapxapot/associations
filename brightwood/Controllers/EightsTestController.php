<?php

namespace Brightwood\Controllers;

use App\Models\TelegramUser;
use Brightwood\Models\Cards\Games\EightsGame;
use Brightwood\Models\Cards\Players\Bot;
use Brightwood\Models\Messages\Interfaces\MessageInterface;
use Brightwood\Parsing\StoryParser;
use Plasticode\Core\Response;
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
        $bot1 = new Bot('Ник');
        $bot2 = new Bot('Майк');
        $bot3 = new Bot('Маргарита');
        $bot4 = new Bot('Мария');

        $game = new EightsGame(
            $bot1,
            $bot2,
            $bot3,
            $bot4
        );

        $parser = new StoryParser();
        $dummyUser = new TelegramUser();

        $result = $game->run();

        $result = array_map(
            fn ($msg) => $parser->parse($dummyUser, $this->wrap($msg)),
            $result
        );

        return Response::text(
            $response,
            Text::join($result)
        );
    }

    private function wrap(MessageInterface $message) : string
    {
        return '<div>' . Text::join($message->lines(), '<br />') . '</div><br />';
    }
}
