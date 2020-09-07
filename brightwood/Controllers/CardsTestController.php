<?php

namespace Brightwood\Controllers;

use Brightwood\Models\Cards\Card;
use Brightwood\Models\Cards\Sets\Decks\FullDeck;
use Plasticode\Core\Response;
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

        $cardNames = $deck
            ->cards()
            ->map(
                fn (Card $c) => $c->name()
            )
            ->toArray();

        return Response::text(
            $response,
            implode(', ', $cardNames)
        );
    }
}
