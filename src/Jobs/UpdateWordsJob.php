<?php

namespace App\Jobs;

use App\Events\WordOutOfDateEvent;
use App\Models\Word;
use Plasticode\Collection;
use Plasticode\Events\EventDispatcher;
use Plasticode\Interfaces\SettingsProviderInterface;

class UpdateWordsJob
{
    /** @var SettingsProviderInterface */
    private $settingsProvider;

    /** @var EventDispatcher */
    private $eventDispatcher;

    public function __construct(
        SettingsProviderInterface $settingsProvider,
        EventDispatcher $eventDispatcher
    )
    {
        $this->settingsProvider = $settingsProvider;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function run() : Collection
    {
        $limit = $this->settingsProvider->getSettings('words.update.limit');
        $ttl = $this->settingsProvider->getSettings('words.update.ttl_min');

        $outOfDate = Word::getOutOfDate($ttl)
            ->limit($limit)
            ->all();

        foreach ($outOfDate as $word) {
            $event = new WordOutOfDateEvent($word);
            $this->eventDispatcher->dispatch($event);
        }

        return $outOfDate;
    }
}
