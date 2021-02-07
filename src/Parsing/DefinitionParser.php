<?php

namespace App\Parsing;

use App\External\DictionaryApi;
use App\Models\Definition;
use App\Semantics\Definition\DefinitionAggregate;
use App\Semantics\Definition\DefinitionEntry;

class DefinitionParser
{
    public function parse(Definition $definition): ?DefinitionAggregate
    {
        switch ($definition->source) {
            case DictionaryApi::SOURCE:
                return $this->parseDictionaryApi($definition->jsonData);
        }

        return null;
    }

    private function parseDictionaryApi(string $jsonData): ?DefinitionAggregate
    {
        $data = json_decode($jsonData, true);

        if (!is_array($data)) {
            return null;
        }

        $result = new DefinitionAggregate();

        foreach ($data as $entry) {
            $defEntry = new DefinitionEntry();

            $meanings = $entry['meanings'] ?? [];

            foreach ($meanings as $meaning) {
                $definitions = $meaning['definitions'] ?? [];

                foreach ($definitions as $definitionEntry) {
                    $definition = $definitionEntry['definition'] ?? null;

                    if (strlen($definition) > 0) {
                        $defEntry->addDefinition($definition);
                    }
                }
            }

            if (!$defEntry->isEmpty()) {
                $result->addEntry($defEntry);
            }
        }

        return !$result->isEmpty()
            ? $result
            : null;
    }
}
