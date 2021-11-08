<?php

namespace App\Models;

use InvalidArgumentException;
use Webmozart\Assert\Assert;

class AggregatedAssociation extends Association
{
    private ?Word $anchor = null;

    public function __construct(Association $association, ?Word $anchor = null)
    {
        parent::__construct($association->toArray());

        $this
            ->withUrl($association->url())
            ->withCreator($association->creator())
            ->withLanguage($association->language())
            ->withMe($association->me())
            ->withTurns($association->turns())
            ->withCanonical($association->canonical())
            ->withFeedbacks($association->feedbacks())
            ->withFirstWord($association->firstWord())
            ->withOverrides($association->overrides())
            ->withSecondWord($association->secondWord());

        if ($anchor !== null) {
            $this->withAnchor($anchor);
        }
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
}
