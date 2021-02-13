<?php

namespace App\Services;

use App\Events\Definition\DefinitionUpdatedEvent;
use App\External\Interfaces\DefinitionSourceInterface;
use App\Models\Definition;
use App\Models\Word;
use App\Parsing\DefinitionParser;
use App\Repositories\Interfaces\DefinitionRepositoryInterface;
use Plasticode\Events\EventDispatcher;

/**
 * @emits DefinitionUpdatedEvent
 */
class DefinitionService
{
    private DefinitionRepositoryInterface $definitionRepository;
    private DefinitionSourceInterface $definitionSource;
    private DefinitionParser $definitionParser;
    private EventDispatcher $eventDispatcher;

    public function __construct(
        DefinitionRepositoryInterface $definitionRepository,
        DefinitionSourceInterface $definitionSource,
        DefinitionParser $definitionParser,
        EventDispatcher $eventDispatcher
    )
    {
        $this->definitionRepository = $definitionRepository;
        $this->definitionSource = $definitionSource;
        $this->definitionParser = $definitionParser;
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
        $definition = $this->definitionRepository->getByWord($word);

        if ($definition !== null && !$allowRemoteLoad) {
            return $definition;
        }

        // no word found, trying loading from the source
        $defData = $this
            ->definitionSource
            ->request(
                $word->language()->code,
                $word->word
            );

        if ($defData === null) {
            return null;
        }

        $definition = $this->definitionRepository->store(
            [
                'source' => $defData->source(),
                'url' => $defData->url(),
                'json_data' => $defData->jsonData(),
                'word_id' => $word->getId(),
            ]
        );

        $parsedDefinition = $this->definitionParser->parse($definition);

        $definition->valid = ($parsedDefinition !== null ? 1 : 0);

        $this->definitionRepository->save($definition);

        $this->eventDispatcher->dispatch(
            new DefinitionUpdatedEvent($definition)
        );

        return $definition;
    }
}
