<?php

namespace App\Jobs;

use App\Events\WordOutOfDateEvent;
use App\Models\Word;
use Plasticode\Collection;
use Plasticode\Core\Interfaces\SettingsProviderInterface;
use Plasticode\Events\EventDispatcher;

class UpdateWordsJob
{
    /** @var SettingsProviderInterface */
    private $settingsProvider;

    /** @var EventDispatcher */
    private $dispatcher;

    public function __construct(
        SettingsProviderInterface $settingsProvider,
        EventDispatcher $dispatcher
    )
    {
        $this->settingsProvider = $settingsProvider;
        $this->dispatcher = $dispatcher;
    }

    public function run() : Collection
    {
        $limit = $this->settingsProvider->get('words.update.limit');
        $ttl = $this->settingsProvider->get('words.update.ttl_min');

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
