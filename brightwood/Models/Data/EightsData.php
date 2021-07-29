<?php

namespace Brightwood\Models\Data;

use App\Bots\Factories\MessageRendererFactory;
use App\Models\TelegramUser;
use Brightwood\Collections\Cards\PlayerCollection;
use Brightwood\Models\Cards\Games\EightsGame;
use Brightwood\Models\Cards\Players\Bot;
use Brightwood\Models\Cards\Players\FemaleBot;
use Brightwood\Models\Cards\Players\Human;
use Brightwood\Parsing\StoryParser;
use Brightwood\Serialization\Interfaces\SerializableInterface;
use Brightwood\Serialization\UniformSerializer;
use Plasticode\Util\Cases;
use Webmozart\Assert\Assert;

/**
 * @property integer $playerCount
 */
class EightsData extends StoryData implements SerializableInterface
{
    /**
     * Optional, BUT - either init using initGame() or set using withGame().
     */
    private ?EightsGame $game = null;

    public function game() : EightsGame
    {
        Assert::notNull($this->game);

        return $this->game;
    }

    /**
     * @return $this
     */
    public function withGame(?EightsGame $game) : self
    {
        $this->game = $game;

        if ($game) {
            $this->playerCount = $game->players()->count();
        }

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
    public function initGame(TelegramUser $tgUser) : self
    {
        $botCount = $this->playerCount - 1;

        $human = new Human($tgUser);

        $players = $this
            ->fetchBots($botCount)
            ->add($human)
            ->shuffle();

        $game = new EightsGame(
            // todo: should be provided by container definitions (a factory!)
            new StoryParser(
                new MessageRendererFactory()
            ),
            new Cases(),
            $players
        );

        return $this
            ->withGame(
                $game->withObserver($human)
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

    // SerializableInterface

    public function jsonSerialize()
    {
        return $this->serialize();
    }

    /**
     * @param array[] $data
     */
    public function serialize(array ...$data) : array
    {
        return UniformSerializer::serialize(
            $this,
            parent::jsonSerialize(),
            ['game' => $this->game],
            ...$data
        );
    }
}
