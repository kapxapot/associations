<?php

namespace Brightwood\Models\Messages;

use Brightwood\Collections\MessageCollection;
use Brightwood\Collections\StoryMessageCollection;
use Brightwood\Models\Data\StoryData;
use Brightwood\Models\Messages\Interfaces\MessageInterface;
use Brightwood\Models\Messages\Interfaces\SequencableInterface;

class StoryMessageSequence implements SequencableInterface
{
    private MessageCollection $messages;

    /** @var string[] */
    private array $actions;

    private ?StoryData $data = null;

    private bool $isFinalized = false;

    public function __construct(
        MessageInterface ...$messages
    )
    {
        $this->messages = MessageCollection::make($messages);
        $this->actions = [];
    }

    public function messages() : MessageCollection
    {
        return $this->messages;
    }

    public function storyMessages() : StoryMessageCollection
    {
        return $this->messages->storyMessages();
    }

    public function isFinalized() : bool
    {
        return $this->isFinalized;
    }

    public function add(MessageInterface ...$messages) : self
    {
        $this->messages = $this->messages->add(...$messages);

        return $this;
    }

    /**
     * "Mashes" together messages and sequences, returning a resulting sequence.
     */
    public static function mash(SequencableInterface ...$items) : self
    {
        $sequence = new self();

        foreach ($items as $item) {
            if ($item instanceof MessageInterface) {
                $sequence->add($item);
            }

            if ($item instanceof self) {
                $sequence = $sequence->merge($item);
            }
        }

        return $sequence;
    }

    /**
     * Creates an empty sequence.
     */
    public static function empty() : self
    {
        return new self();
    }

    public function isEmpty() : bool
    {
        return $this->messages->isEmpty();
    }

    /**
     * Prepends text prefix to the first message.
     * If there are no messages, adds a new one.
     */
    public function prependPrefix(?string $prefix) : self
    {
        if (strlen($prefix) == 0) {
            return $this;
        }

        /** @var MessageInterface|null */
        $first = $this->messages->first();

        if ($first) {
            $first->prependLines($prefix);
        } else {
            $this->add(
                new TextMessage($prefix)
            );
        }

        return $this;
    }

    /**
     * Overrides actions.
     */
    public function withActions(string ...$actions) : self
    {
        $last = $this->messages->last();

        if ($last) {
            $last->appendActions(...$actions);
        } else {
            $this->actions = $actions;
        }

        return $this;
    }

    /**
     * Overrides data.
     */
    public function withData(StoryData $data) : self
    {
        $this->data = $data;

        return $this;
    }

    public function finalize(bool $state = true) : self
    {
        $this->isFinalized = $state;

        return $this;
    }

    public function nodeId() : ?int
    {
        /** @var StoryMessage|null */
        $last = $this->storyMessages()->last();

        return $last
            ? $last->nodeId()
            : null;
    }

    public function actions() : array
    {
        if (!empty($this->actions)) {
            return $this->actions;
        }

        /** @var MessageInterface|null */
        $last = $this
            ->messages
            ->last(
                fn (MessageInterface $m) => $m->hasActions()
            );

        return $last
            ? $last->actions()
            : [];
    }

    public function data() : ?StoryData
    {
        if ($this->data) {
            return $this->data;
        }

        /** @var MessageInterface|null */
        $last = $this
            ->messages
            ->last(
                fn (MessageInterface $m) => $m->hasData()
            );

        return $last
            ? $last->data()
            : null;
    }

    public function overrideActions() : array
    {
        return $this->actions;
    }

    public function overrideData() : ?StoryData
    {
        return $this->data;
    }

    /**
     * Concats messages and creates a NEW sequence.
     * 
     * If there are overrides or other attributes (such as isFinalized),
     * they are taken from the added sequence (!).
     */
    public function merge(self $other) : self
    {
        $sequence = new self(...$this->messages);
        $sequence->add(...$other->messages());

        if (!empty($other->overrideActions())) {
            $sequence->withActions(
                ...$other->overrideActions()
            );
        }

        if ($other->overrideData()) {
            $sequence->withData(
                $other->overrideData()
            );
        }

        return $sequence->finalize(
            $other->isFinalized()
        );
    }
}
