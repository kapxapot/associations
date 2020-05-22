<?php

namespace App\Jobs;

use App\Collections\WordCollection;
use App\Events\WordOutOfDateEvent;
use App\Repositories\Interfaces\WordRepositoryInterface;
use Plasticode\Core\Interfaces\SettingsProviderInterface;
use Plasticode\Events\EventDispatcher;

class UpdateWordsJob
{
    private WordRepositoryInterface $wordRepository;

    private SettingsProviderInterface $settingsProvider;
    private EventDispatcher $eventDispatcher;

    public function __construct(
        WordRepositoryInterface $wordRepository,
        SettingsProviderInterface $settingsProvider,
        EventDispatcher $eventDispatcher
    )
    {
        $this->wordRepository = $wordRepository;

        $this->settingsProvider = $settingsProvider;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function run() : WordCollection
    {
        $ttl = $this->settingsProvider
            ->get('words.update.ttl_min');

        $limit = $this->settingsProvider
            ->get('words.update.limit');

        $outOfDate = $this
            ->wordRepository
            ->getAllOutOfDate($ttl, $limit);

        foreach ($outOfDate as $word) {
            $event = new WordOutOfDateEvent($word);
            $this->eventDispatcher->dispatch($event);
        }

        return $outOfDate;
    }
}
