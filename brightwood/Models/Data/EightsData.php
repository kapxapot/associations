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
    /**
     * Required - either set using the constructor or withHuman().
     */
    private ?Human $human;

    /**
     * Required - either init using initGame() or set using withGame().
     */
    private ?EightsGame $game = null;

    public function __construct(
        ?Human $human = null,
        ?array $data = null
    )
    {
        parent::__construct($data);

        $this->withHuman($human);
    }

    private function human() : Human
    {
        Assert::notNull($this->human);

        return $this->human;
    }

    /**
     * @return $this
     */
    public function withHuman(?Human $human) : self
    {
        $this->human = $human;

        return $this;
    }

    public function game() : EightsGame
    {
        Assert::notNull($this->game);

        return $this->game;
    }

    /**
     * @return $this
     */
    public function withGame(EightsGame $game) : self
    {
        $this->game = $game;
        $this->playerCount = $game->players()->count();

        return $this;
    }

    protected function init() : void
    {
        $this->playerCount = EightsGame::minPlayers();
    }

    /**
     * @return $this
     */
    public function withPlayerCount(int $playerCount) : self
    {
        Assert::range(
            $playerCount,
            EightsGame::minPlayers(),
            EightsGame::maxPlayers()
        );

        $this->playerCount = $playerCount;

        return $this;
    }

    /**
     * @return $this
     */
    public function initGame() : self
    {
        $botCount = $this->playerCount - 1;

        $players = $this
            ->fetchBots($botCount)
            ->add($this->human())
            ->shuffle();

        $game = new EightsGame(
            new StoryParser(),
            new Cases(),
            $players
        );

        return $this
            ->withGame(
                $game->withObserver($this->human())
            );
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

    public function jsonSerialize()
    {
        return array_merge(
            parent::jsonSerialize(),
            [
                'human_id' => $this->human()->id(),
                'game' => $this->game,
            ]
        );
    }
}
