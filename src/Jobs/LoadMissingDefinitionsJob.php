<?php

namespace App\Jobs;

use App\Collections\DefinitionCollection;
use App\Jobs\Interfaces\ModelJobInterface;
use App\Models\Word;
use App\Repositories\Interfaces\WordRepositoryInterface;
use App\Services\DefinitionService;
use Plasticode\Settings\Interfaces\SettingsProviderInterface;

/**
 * For all words without dict word load dict words.
 */
class LoadMissingDefinitionsJob implements ModelJobInterface
{
    private WordRepositoryInterface $wordRepository;
    private DefinitionService $definitionService;
    private SettingsProviderInterface $settingsProvider;

    public function __construct(
        WordRepositoryInterface $wordRepository,
        DefinitionService $definitionService,
        SettingsProviderInterface $settingsProvider
    )
    {
        $this->wordRepository = $wordRepository;
        $this->definitionService = $definitionService;
        $this->settingsProvider = $settingsProvider;
    }

    public function run(): DefinitionCollection
    {
        $limit = $this
            ->settingsProvider
            ->get('jobs.load_missing_definitions.batch_size', 10);

        return DefinitionCollection::from(
            $this
                ->wordRepository
                ->getAllUndefined($limit)
                ->map(
                    fn (Word $w) => $this->definitionService->loadByWord($w)
                )
        );
    }
}
