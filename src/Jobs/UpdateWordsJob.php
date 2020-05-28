<?php

namespace App\Jobs;

use App\Collections\WordCollection;
use App\Events\Word\WordOutOfDateEvent;
use App\Jobs\Interfaces\ModelJobInterface;
use App\Repositories\Interfaces\WordRepositoryInterface;
use Plasticode\Core\Interfaces\SettingsProviderInterface;
use Plasticode\Events\EventDispatcher;

class UpdateWordsJob implements ModelJobInterface
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
        $ttl = $this
            ->settingsProvider
            ->get('jobs.update_words.ttl_min', 1440);

        $limit = $this
            ->settingsProvider
            ->get('jobs.update_words.batch_size', 10);

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
