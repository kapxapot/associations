<?php

namespace Brightwood\Collections;

use Brightwood\Models\Links\RedirectLink;

class RedirectLinkCollection extends StoryLinkCollection
{
    protected string $class = RedirectLink::class;

    public function choose() : ?RedirectLink
    {
        return $this->weightedRandom(
            fn (RedirectLink $l) => $l->weight()
        );
    }

    private function weightedRandom(callable $weightFunc) : ?RedirectLink
    {
        if ($this->isEmpty()) {
            return null;
        }

        if ($this->count() == 1) {
            return $this->first();
        }

        $totalWeight = $this->numerize($weightFunc)->sum();

        $rand = mt_rand();
        $randMax = mt_getrandmax();

        $point = $rand / $randMax;

        $weightSum = 0;

        /** @var RedirectLink */
        foreach ($this as $item) {
            $weightSum += $weightFunc($item);
            $normalizedSum = $weightSum / $totalWeight;

            if ($normalizedSum >= $point) {
                return $item;
            }
        }

        // it's expected that the corresponding item should be already returned
        // this is just for safe guard
        return $this->last();
    }
}
