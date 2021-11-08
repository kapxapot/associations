<?php

namespace App\Core;

use App\Models\Association;
use App\Models\Turn;
use App\Models\Word;

class Serializer
{
    public function serializeTurn(?Turn $turn) : ?array
    {
        if ($turn === null) {
            return null;
        }

        return $this->serializeRaw(
            [
                'game' => [
                    'id' => $turn->gameId,
                    'url' => $turn->game()->url()
                ],
                'turn_id' => $turn->getId(),
                'word' => $turn->word()->word,
                'is_ai' => $turn->isAiTurn(),
                'is_native' => $turn->isNative()
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
                'is_public' => $word->isFuzzyPublic(),
                'scope' => $word->scope,
                'severity' => $word->severity,
                'url' => $word->url(),
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
                'is_public' => $association->isFuzzyPublic(),
                'scope' => $association->scope,
                'severity' => $association->severity,
                'url' => $association->url(),
            ];

            $associationFeedback = $association->feedbackByMe();

            if ($associationFeedback) {
                $array['association']['feedback'] = $associationFeedback;
            }
        }

        return $array;
    }
}
