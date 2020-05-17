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
    private EventDispatcher $dispatcher;

    public function __construct(
        WordRepositoryInterface $wordRepository,
        SettingsProviderInterface $settingsProvider,
        EventDispatcher $dispatcher
    )
    {
        $this->wordRepository = $wordRepository;

        $this->settingsProvider = $settingsProvider;
        $this->dispatcher = $dispatcher;
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
            $this->dispatcher->dispatch($event);
        }

        return $outOfDate;
    }
}
