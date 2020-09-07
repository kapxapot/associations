<?php

namespace Brightwood\Controllers;

use Brightwood\Models\Cards\Card;
use Brightwood\Models\Cards\Sets\Decks\FullDeck;
use Brightwood\Models\Cards\Sets\Hand;
use Brightwood\Models\Cards\Sets\Pile;
use Plasticode\Core\Response;
use Plasticode\Util\Text;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class CardsTestController
{
    public function deck(
        ServerRequestInterface $request,
        ResponseInterface $response
    )
    {
        $deck = new FullDeck();

        $lines = [
            '<b>Deck:</b>',
            $deck->toString()
        ];

        $player1 = new Hand();
        $dealer = new Hand();

        $deck->deal([$player1, $dealer], 7);

        $discardPile = new Pile();
        $deck->deal([$discardPile], 1);

        $stockPile = $deck->toPile();

        $lines = [
            ...$lines,
            '<b>Player1\'s hand:</b>',
            $player1->toString(),
            '<b>Dealer\'s hand:</b>',
            $dealer->toString(),
            '<b>Discard pile:</b>',
            $discardPile->toString(),
            '<b>Stockpile:</b>',
            $stockPile->toString()
        ];

        $lines = array_map(
            fn ($l) => '<div>' . $l . '</div>',
            $lines
        );

        return Response::text(
            $response,
            Text::join($lines)
        );
    }
}
