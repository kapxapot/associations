<?php

namespace App\Jobs;

use App\Collections\WordCollection;
use App\Events\Word\WordOutOfDateEvent;
use App\Jobs\Interfaces\ModelJobInterface;
use App\Models\Word;
use App\Repositories\Interfaces\WordRepositoryInterface;
use Plasticode\Events\Event;
use Plasticode\Events\EventDispatcher;
use Plasticode\Settings\Interfaces\SettingsProviderInterface;

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

    public function run(): WordCollection
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

        $outOfDate
            ->map(
                fn (Word $w) => new WordOutOfDateEvent($w)
            )
            ->apply(
                fn (Event $e) => $this->eventDispatcher->dispatch($e)
            );

        return $outOfDate;
    }
}
