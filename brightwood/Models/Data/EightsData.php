<?php

namespace Brightwood\Models\Data;

use App\Models\TelegramUser;
use Brightwood\Collections\Cards\PlayerCollection;
use Brightwood\Models\Cards\Games\EightsGame;
use Brightwood\Models\Cards\Players\Bot;
use Brightwood\Models\Cards\Players\FemaleBot;
use Brightwood\Models\Cards\Players\Human;
use Brightwood\Parsing\StoryParser;
use Plasticode\Util\Cases;
use Webmozart\Assert\Assert;

/**
 * @property integer $playerCount
 */
class EightsData extends StoryData
{
    private TelegramUser $tgUser;
    private ?EightsGame $game = null;

    public function __construct(
        TelegramUser $tgUser,
        ?array $data = null
    )
    {
        parent::__construct($data);

        $this->tgUser = $tgUser;
    }

    protected function init() : void
    {
        $this->playerCount = EightsGame::minPlayers();
    }

    public function game() : ?EightsGame
    {
        return $this->game;
    }

    public function setPlayerCount(int $playerCount) : self
    {
        Assert::range(
            $playerCount,
            EightsGame::minPlayers(),
            EightsGame::maxPlayers()
        );

        $this->playerCount = $playerCount;

        return $this;
    }

    public function start() : self
    {
        $human = new Human($this->tgUser);

        $botCount = $this->playerCount - 1;

        $players = $this
            ->fetchBots($botCount)
            ->add($human)
            ->shuffle();

        $this->game = new EightsGame(
            new StoryParser(),
            new Cases(),
            ...$players
        );

        $this->game->withObserver($human);

        return $this;
    }

    private function fetchBots(int $count) : PlayerCollection
    {
        return $this
            ->botPool()
            ->shuffle()
            ->take($count);
    }

    private function botPool() : PlayerCollection
    {
        return PlayerCollection::collect(
            new FemaleBot('Джейн'),
            new FemaleBot('Анна'),
            new FemaleBot('Мария'),
            new FemaleBot('Эмили'),
            new FemaleBot('Лиза'),
            new Bot('Джон'),
            new Bot('Питер'),
            new Bot('Том'),
            new Bot('Гарри'),
            new Bot('Джеймс')
        );
    }
}
