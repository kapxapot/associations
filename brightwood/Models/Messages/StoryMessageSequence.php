<?php

namespace Brightwood\Models\Messages;

use App\Bots\Traits\Vars;
use Brightwood\Collections\MessageCollection;
use Brightwood\Models\Data\StoryData;
use Brightwood\Models\Messages\Interfaces\MessageInterface;
use Brightwood\Models\Messages\Interfaces\SequencableInterface;
use Brightwood\Models\MetaKey;
use Brightwood\Models\Stories\Core\Story;
use Brightwood\Util\Regex;
use Brightwood\Util\Util;
use Plasticode\Collections\Generic\Collection;

class StoryMessageSequence implements SequencableInterface
{
    use Vars;

    private MessageCollection $messages;

    /** @var string[] */
    private array $actions = [];

    private ?StoryData $data = null;
    private array $meta = [];

    private bool $isFinalized = false;
    private bool $isStuck = false;

    public function __construct(MessageInterface ...$messages)
    {
        $this->messages = MessageCollection::make($messages);
    }

    public function messages(): MessageCollection
    {
        return $this->messages;
    }

    public function meta(): array
    {
        return $this->meta;
    }

    /**
     * Is message sequence "finished" (no further interaction is expected).
     */
    public function isFinalized(): bool
    {
        return $this->isFinalized;
    }

    /**
     * Is message sequence "stuck" and cannot continue.
     */
    public function isStuck(): bool
    {
        return $this->isStuck;
    }

    /**
     * @return $this
     */
    public function addText(?string ...$lines): self
    {
        $this->messages = $this->messages->add(
            new TextMessage(...$lines)
        );

        return $this;
    }

    /**
     * @return $this
     */
    public function add(MessageInterface ...$messages): self
    {
        $this->messages = $this->messages->add(...$messages);

        return $this;
    }

    /**
     * Makes a finalized sequence from one TextMessage.
     */
    public static function textFinalized(?string ...$lines): self
    {
        return self::text(...$lines)->finalize();
    }

    /**
     * Makes a finalized sequence.
     */
    public static function makeFinalized(MessageInterface ...$messages): self
    {
        return self::make(...$messages)->finalize();
    }

    /**
     * Makes a stuck sequence from one TextMessage.
     */
    public static function textStuck(?string ...$lines): self
    {
        return self::text(...$lines)->stuck();
    }

    /**
     * Makes a sequence from one TextMessage.
     */
    public static function text(?string ...$lines): self
    {
        return self::make(
            new TextMessage(...$lines)
        );
    }

    /**
     * Shortcut for constructor.
     */
    public static function make(MessageInterface ...$messages): self
    {
        return new self(...$messages);
    }

    /**
     * "Mashes" together messages and sequences, returning a resulting sequence.
     */
    public static function mash(?SequencableInterface ...$items): self
    {
        $sequence = new self();

        foreach ($items as $item) {
            if (!$item) {
                continue;
            }

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
    public static function empty(): self
    {
        return new self();
    }

    public function isEmpty(): bool
    {
        return $this->messages->isEmpty();
    }

    /**
     * Returns the sequence itself if it's not empty. Otherwise, returns `$other`.
     *
     * @return $this|self
     */
    public function or(self $other): self
    {
        return $this->isEmpty()
            ? $other
            : $this;
    }

    /**
     * Prepends text prefix to the first message.
     * If there are no messages, adds a new one.
     */
    public function prependMessage(?string $prefix): self
    {
        if (strlen($prefix) === 0) {
            return $this;
        }

        /** @var MessageInterface|null */
        $first = $this->messages->first();

        if ($first) {
            $first->prependLines($prefix);
        } else {
            $this->addText($prefix);
        }

        return $this;
    }

    /**
     * Appends actions to the last message if there is any.
     * Otherwise, sets them to the sequence itself.
     *
     * @return $this
     */
    public function withActions(?string ...$actions): self
    {
        /** @var MessageInterface|null */
        $last = $this->messages->last();

        if ($last) {
            $last->appendActions(...$actions);
        } else {
            $this->actions = Util::clean($actions);
        }

        return $this;
    }

    /**
     * Overrides data.
     *
     * @return $this
     */
    public function withData(StoryData $data): self
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @return $this
     */
    public function withMetaValue(string $key, $value): self
    {
        $this->meta[$key] = $value;
        return $this;
    }

    /**
     * @return $this
     */
    public function withMeta(array $meta): self
    {
        foreach ($meta as $key => $value) {
            $this->withMetaValue($key, $value);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function withStage(string $stage): self
    {
        return $this->withMetaValue(MetaKey::STAGE, $stage);
    }

    /**
     * @return $this
     */
    public function withStory(Story $story): self
    {
        return $this->withMetaValue(MetaKey::STORY_ID, $story->getId());
    }

    /**
     * Changes `isFinalized` property.
     *
     * @return $this
     */
    public function finalize(bool $state = true): self
    {
        $this->isFinalized = $state;
        return $this;
    }

    /**
     * Changes `isStuck` property.
     *
     * @return $this
     */
    public function stuck(bool $state = true): self
    {
        $this->isStuck = $state;
        return $this;
    }

    public function nodeId(): ?int
    {
        /** @var StoryMessage|null */
        $last = $this
            ->messages
            ->where(
                fn (MessageInterface $message) => $message instanceof StoryMessage
            )
            ->last(
                fn (StoryMessage $m) => $m->nodeId() > 0
            );

        return $last
            ? $last->nodeId()
            : null;
    }

    /**
     * @return string[]
     */
    public function actions(): array
    {
        if (!empty($this->actions)) {
            return $this->actions;
        }

        /** @var MessageInterface|null */
        $lastActionMessage = $this
            ->messages
            ->last(
                fn (MessageInterface $m) => $m->hasActions()
            );

        return $lastActionMessage
            ? $lastActionMessage->actions()
            : [];
    }

    public function data(): ?StoryData
    {
        // if sequence itself has data, return it
        if ($this->data) {
            return $this->data;
        }

        // otherwise, look for the last message with data and return it
        /** @var MessageInterface|null */
        $lastWithData = $this
            ->messages
            ->last(
                fn (MessageInterface $m) => $m->hasData()
            );

        return $lastWithData
            ? $lastWithData->data()
            : null;
    }

    /**
     * Returns the actions set on the sequence itself.
     */
    public function ownActions(): array
    {
        return $this->actions;
    }

    /**
     * Returns the data set on the sequence itself.
     */
    public function ownData(): ?StoryData
    {
        return $this->data;
    }

    /**
     * Concats messages and creates a NEW sequence if `$other` is not null.
     * Otherwise, returns the sequence itself.
     *
     * @return $this|self
     */
    public function merge(?self $other): self
    {
        if (!$other) {
            return $this;
        }

        // messages - merge
        $sequence = new self(...$this->messages);
        $sequence->add(...$other->messages());

        // actions - override only own actions
        if (!empty($other->ownActions())) {
            $sequence->withActions(
                ...$other->ownActions()
            );
        }

        // data - override only own data
        if ($other->ownData()) {
            $sequence->withData(
                $other->ownData()
            );
        }

        // vars - merge
        $sequence->withVars($this->vars());
        $sequence->withVars($other->vars());

        // meta - merge
        $sequence->withMeta($this->meta());
        $sequence->withMeta($other->meta());

        // finalized & stuck - override
        return $sequence
            ->finalize(
                $other->isFinalized()
            )
            ->stuck(
                $other->isStuck()
            );
    }

    public function hasText(): bool
    {
        return $this->messages->anyFirst(
            fn (MessageInterface $m) => Collection::make($m->lines())
                ->anyFirst(
                    fn ($s) => strlen($s) > 0
                )
        );
    }

    public function hasActions(): bool
    {
        return !empty($this->actions);
    }

    /**
     * Splits messages with images into separate messages.
     *
     * If an image is found, it starts a new message. All previous lines are put into a TextMessage.
     */
    public function splitImageMessages(): MessageCollection
    {
        $splitMessages = [];

        /** @var MessageInterface $message */
        foreach ($this->messages as $message) {
            $lines = $message->lines();
            $imageIndexes = [];

            foreach ($lines as $index => $line) {
                if (Regex::isImageUrl($line)) {
                    $imageIndexes[] = $index;
                }
            }

            $imageCount = count($imageIndexes);

            if (!$imageCount) {
                $splitMessages[] = $message;
                continue;
            }

            // e.g., we have an array of 10 lines
            // and we have 3 image indexes: 2, 4, 7
            // we will split the array into 4 messages:
            // - lines 0-1 (start .. index1 - 1)
            // - lines 2-3 (index1 .. index2 - 1)
            // - lines 4-6 (index2 .. index3 - 1)
            // - lines 7-9 (index3 .. end)

            // if the first message is not an image,
            // cut the beginning of the array and create a text message
            $firstIndex = $imageIndexes[0];

            if ($firstIndex > 0) {
                $slice = array_slice($lines, 0, $firstIndex);
                $splitMessages[] = new TextMessage(...$slice);
            }

            for ($i = 0; $i < $imageCount; $i++) {
                $index = $imageIndexes[$i];
                $image = $lines[$index];
                $index++;

                if ($i < $imageCount - 1) {
                    // not last
                    $nextIndex = $imageIndexes[$i + 1];
                    $slice = array_slice($lines, $index, $nextIndex - $index);
                    $splitMessage = new TextMessage(...$slice);
                } else {
                    // last
                    $slice = array_slice($lines, $index);
                    $splitMessage = $message->withLines(...$slice);
                }

                $splitMessages[] = $splitMessage->withImage($image);
            }
        }

        return MessageCollection::make($splitMessages);
    }

    private function hasMetaKey(string $key): bool
    {
        return array_key_exists($key, $this->meta);
    }
}
