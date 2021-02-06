<?php

namespace App\Services;

use App\Events\Definition\DefinitionUpdatedEvent;
use App\External\Interfaces\DefinitionSourceInterface;
use App\Models\Definition;
use App\Models\Word;
use App\Repositories\Interfaces\DefinitionRepositoryInterface;
use Plasticode\Events\EventDispatcher;

/**
 * @emits DefinitionUpdatedEvent
 */
class DefinitionService
{
    private DefinitionRepositoryInterface $definitionRepository;
    private DefinitionSourceInterface $definitionSource;
    private EventDispatcher $eventDispatcher;

    public function __construct(
        DefinitionRepositoryInterface $definitionRepository,
        DefinitionSourceInterface $definitionSource,
        EventDispatcher $eventDispatcher
    )
    {
        $this->definitionRepository = $definitionRepository;
        $this->definitionSource = $definitionSource;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Returns (and loads it from the source) word definition by {@see Word} entity.
     */
    public function loadByWord(Word $word): ?Definition
    {
        return $this->getByWord($word, true);
    }

    /**
     * Returns word definition by {@see Word} entity.
     * 
     * @param $allowRemoteLoad Set this to `true` if loading from source must
     * be enabled. By default it's not performed.
     */
    public function getByWord(
        Word $word,
        bool $allowRemoteLoad = false
    ): ?Definition
    {
        // searching by word
        $definition = $word
            ? $this->definitionRepository->getByWord($word)
            : null;

        if ($definition === null && $allowRemoteLoad) {
            // no word found, loading from the source
            $defData = $this
                ->definitionSource
                ->request($word->language(), $word->word);

            $definition = $this->definitionRepository->create(
                [
                    'source' => $defData->source(),
                    'url' => $defData->url(),
                    'data' => $defData->data(),
                    'word_id' => $word->getId(),
                ]
            );

            $this->eventDispatcher->dispatch(
                new DefinitionUpdatedEvent($definition)
            );
        }

        return $definition;
    }
}
