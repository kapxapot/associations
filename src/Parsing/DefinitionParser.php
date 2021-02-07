<?php

namespace App\Parsing;

use App\External\DictionaryApi;
use App\Models\Definition;

class DefinitionParser
{
    public function parse(Definition $definition): ?string
    {
        switch ($definition->source) {
            case DictionaryApi::SOURCE:
                return $this->parseDictionaryApi($definition->jsonData);
        }

        return null;
    }

    private function parseDictionaryApi(string $jsonData): ?string
    {
        $data = json_decode($jsonData, true);

        if (!is_array($data)) {
            return null;
        }

        /** @var string[] $result */
        $results = [];

        foreach ($data as $entry) {
            $meanings = $entry['meanings'] ?? [];

            foreach ($meanings as $meaning) {
                $definitions = $meaning['definitions'] ?? [];

                foreach ($definitions as $definitionEntry) {
                    $definition = $definitionEntry['definition'] ?? null;

                    if (strlen($definition) > 0) {
                        $results[] = $definition;
                    }
                }
            }
        }

        return !empty($results)
            ? implode('; ', $results)
            : null;
    }
}
