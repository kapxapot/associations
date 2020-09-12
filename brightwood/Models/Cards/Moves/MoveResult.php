<?php

namespace Brightwood\Models\Cards\Moves;

use Brightwood\Collections\Cards\ActionCollection;
use Brightwood\Models\Cards\Card;
use Brightwood\Models\Cards\Moves\Actions\Action;
use Brightwood\Models\Cards\Moves\Actions\GiftAction;

class MoveResult
{
    private ActionCollection $actions;

    public function __construct(Action ...$actions)
    {
        $this->actions = ActionCollection::make($actions);
    }

    public function actions() : ActionCollection
    {
        return $this->actions;
    }

    /**
     * @return static
     */
    public function addAction(Action $action) : self
    {
        $this->actions = $this->actons->add($action);

        return $this;
    }

    public function hasGift() : bool
    {
        return $this->giftAction() !== null;
    }

    public function giftCard() : ?Card
    {
        $ga = $this->giftAction();

        return $ga
            ? $ga->card()
            : null;
    }

    /**
     * Returns the first GiftAction if present.
     */
    private function giftAction() : ?GiftAction
    {
        return $this->actions->first(
            fn (Action $a) => $a instanceof GiftAction
        );
    }
}
