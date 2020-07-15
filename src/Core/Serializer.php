<?php

namespace App\Core;

use App\Core\Interfaces\LinkerInterface;
use App\Models\Association;
use App\Models\Turn;
use App\Models\Word;

class Serializer
{
    private LinkerInterface $linker;

    public function __construct(
        LinkerInterface $linker
    )
    {
        $this->linker = $linker;
    }

    public function serializeTurn(Turn $turn) : array
    {
        return $this->serializeRaw(
            [
                'game' => [
                    'id' => $turn->gameId,
                    'url' => $turn->game()->url()
                ],
                'turn_id' => $turn->getId(),
                'word' => $turn->word()->word,
                'is_ai' => $turn->isAiTurn()
            ],
            $turn->word(),
            $turn->association()
        );
    }

    public function serializeRaw(array $array, Word $word, ?Association $association) : array
    {
        $array = array_merge(
            $array,
            [
                'id' => $word->getId(),
                'is_approved' => $word->isApproved(),
                'url' => $this->linker->abs($word->url()),
                'display_name' => $word->displayName(),
            ]
        );

        $wordFeedback = $word->feedbackByMe();

        if ($wordFeedback) {
            $array['feedback'] = $wordFeedback;

            $array['feedback']['duplicate_word'] = $wordFeedback->hasDuplicate()
                ? $wordFeedback->duplicate()->word
                : null;
        }

        if ($association) {
            $array['association'] = [
                'id' => $association->getId(),
                'is_approved' => $association->isApproved(),
                'url' => $this->linker->abs($association->url()),
                'feedback' => $association->feedbackByMe()
            ];
        }

        return $array;
    }
}
