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
        $array['id'] = $word->getId();
        $array['is_approved'] = $word->isApproved();
        $array['url'] = $this->linker->abs($word->url());
        $array['display_name'] = $word->displayName();

        if ($association) {
            $array['association'] = [
                'id' => $association->getId(),
                'is_approved' => $association->isApproved(),
                'url' => $this->linker->abs($association->url()),
            ];
        }

        return $array;
    }
}
