<?php

namespace App\Jobs;

use Plasticode\Contained;

use App\Models\Word;

class UpdateWordsJob extends Contained
{
    public function run()
    {
        $wordApprovedLimit = 10;
        $wordMatureLimit = 10;

        $wordApprovedTtl = new \DateInterval('1 day');
        $wordMatureTtl = new \DateInterval('1 day');

        $oldestApprovedWords = Word::getOldestApproved($wordApprovedTtl)
            ->limit($wordApprovedLimit)
            ->all();
        
        $oldestMatureWords = Word::getOldestMature($wordMatureTtl)
            ->limit($wordMatureLimit)
            ->all();

        foreach ($oldestApprovedWords as $word) {
            $event = new WordOutOfDateEvent($word);
            $this->dispatcher->dispatch($event);
        }

        foreach ($oldestMatureWords as $word) {
            $event = new WordOutOfDateEvent($word);
            $this->dispatcher->dispatch($event);
        }
    }
}
