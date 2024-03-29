<?php

namespace App\Models;

use InvalidArgumentException;
use JsonSerializable;
use Plasticode\Models\Interfaces\EquatableInterface;
use Plasticode\Util\Text;
use Webmozart\Assert\Assert;

class AggregatedAssociation extends Association implements JsonSerializable
{
    private Association $association;

    /**
     * Anchor is the "left" word in the association.
     */
    private ?Word $anchor = null;

    private bool $junky = false;

    /** @var string[] $log */
    private array $log = [];

    public function __construct(Association $association, ?Word $anchor = null)
    {
        parent::__construct($association->toArray());

        $this->association = $association;

        $this
            ->withUrl(fn () => $association->url())
            ->withCreator(fn () => $association->creator())
            ->withLanguage(fn () => $association->language())
            ->withMe(fn () => $association->me())
            ->withTurns(fn () => $association->turns())
            ->withCanonical(fn () => $association->canonical())
            ->withFeedbacks(fn () => $association->feedbacks())
            ->withFirstWord(fn () => $association->firstWord())
            ->withOverrides(fn () => $association->overrides())
            ->withSecondWord(fn () => $association->secondWord());

        if ($anchor !== null) {
            $this->withAnchor($anchor);
        }
    }

    public function association(): Association
    {
        return $this->association;
    }

    /**
     * `equals()` override that takes anchor into account.
     */
    public function equals(?EquatableInterface $obj): bool
    {
        return parent::equals($obj)
            && ($obj instanceof self)
            && (!$this->hasAnchor() && !$obj->hasAnchor()
                || $this->anchor->equals($obj->anchor()));
    }

    public function anchorEquals(Word $word): bool
    {
        return $this->hasAnchor() && $this->anchor->equals($word);
    }

    public function hasAnchor(): bool
    {
        return $this->anchor !== null;
    }

    public function anchor(): ?Word
    {
        return $this->anchor;
    }

    /**
     * Sets anchor only if there is none.
     *
     * @return $this
     */
    public function withSoftAnchor(Word $anchor): self
    {
        return $this->hasAnchor()
            ? $this
            : $this->withAnchor($anchor);
    }

    /**
     * @return $this
     */
    public function withAnchor(Word $anchor): self
    {
        $this->anchor = $anchor;

        return $this;
    }

    /**
     * @return $this
     */
    public function withJunky(bool $junky): self
    {
        $this->junky = $junky;

        return $this;
    }

    /**
     * Returns other than anchor word.
     *
     * In case of no anchor throws an {@see InvalidArgumentException}
     *
     * @throws InvalidArgumentException
     */
    public function otherThanAnchor(): Word
    {
        Assert::notNull($this->anchor);

        return $this->firstWord()->equals($this->anchor)
            ? $this->secondWord()
            : $this->firstWord();
    }

    public function isJunky(): bool
    {
        return $this->junky;
    }

    public function markAsJunky(): void
    {
        $this->junky = true;
    }

    public function log(): string
    {
        return Text::join($this->log, ', ');
    }

    public function addToLog(string $message): void
    {
        $this->log[] = $message;
    }

    // JsonSerializable

    public function jsonSerialize()
    {
        return [
            $this->getId(),
            $this->anchor()->getId(),
            $this->isJunky(),
            $this->log(),
        ];
    }
}
