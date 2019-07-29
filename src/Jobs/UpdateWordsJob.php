<?php

namespace App\Jobs;

use App\Events\WordOutOfDateEvent;
use App\Models\Word;
use Plasticode\Collection;
use Plasticode\Contained;

class UpdateWordsJob extends Contained
{
    public function run() : Collection
    {
        $limit = $this->getSettings('words.update.limit');
        $ttl = $this->getSettings('words.update.ttl_min');

        $outOfDate = Word::getOutOfDate($ttl)
            ->limit($limit)
            ->all();

        foreach ($outOfDate as $word) {
            $event = new WordOutOfDateEvent($word);
            $this->dispatcher->dispatch($event);
        }

        return $outOfDate;
    }
}
