<?php

namespace Brightwood\Controllers;

use Brightwood\Collections\Cards\PlayerCollection;
use Brightwood\Models\Cards\Games\CardGame;
use Brightwood\Models\Cards\Players\Bot;
use Brightwood\Models\Cards\Players\Player;
use Brightwood\Models\Cards\Sets\CardList;
use Brightwood\Models\Cards\Sets\Pile;
use Brightwood\Testing\Cards\TestDeckFactory;
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
        $bot1 = new Bot('Bot1');
        $bot2 = new Bot('Bot2');

        $game = new CardGame(
            (new TestDeckFactory())->make(),
            new Pile(),
            PlayerCollection::collect($bot1, $bot2)
        );

        $lines = [
            $this->cardsStr($game->deck(), 'Starting deck')
        ];

        $lines[] = $this->wrap(
            'Dealing 3 cards to every player, drawing 1 card to discard...'
        );

        $game->deal(3);
        $game->drawToDiscard();
        $lines = array_merge($lines, $this->status($game));

        $lines[] = $this->wrap('One card from deck to discard...');
        $game->drawToDiscard();
        $lines = array_merge($lines, $this->status($game));

        $lines[] = $this->wrap('Bot1 draws 2 cards from deck...');
        $game->drawToHand($bot1, 2);
        $lines = array_merge($lines, $this->status($game));

        $lines[] = $this->wrap('Bot2 takes 1 card from discard...');
        $game->takeFromDiscard($bot2);
        $lines = array_merge($lines, $this->status($game));

        return Response::text(
            $response,
            Text::join($lines)
        );
    }

    /**
     * @return string[]
     */
    private function status(CardGame $game) : array
    {
        return [
            ...$game
                ->players()
                ->map(
                    fn (Player $p) => $this->cardsStr($p->hand(), $p->name() . '\'s hand')
                )
                ->toArray(),
            $this->cardsStr($game->discard(), 'Discard pile'),
            $this->cardsStr($game->deck(), 'Deck'),
        ];
    }

    private function cardsStr(CardList $list, string $label) : string
    {
        return $this->wrap(
            '<b>' . $label . ' (' . $list->size() . '):</b><br/>' . $list
        );
    }

    private function wrap(string $line) : string
    {
        return '<div>' . $line . '</div><br />';
    }
}
