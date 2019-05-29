<?php

namespace App\Services;

use Plasticode\Contained;
use Plasticode\Util\Date;

use App\Models\Turn;

class TurnService extends Contained
{
    public function finish(Turn $turn, $finishDate = null)
    {
        if ($turn->isFinished()) {
            return;
        }
        
        $turn->finishedAt = $finishDate ?? Date::dbNow();
        $turn->save();
    }
}
