<?php

namespace App\Parsing;

use App\External\DictionaryApi;
use App\Models\Definition;
use App\Models\Language;
use App\Semantics\Definition\DefinitionAggregate;
use App\Semantics\Definition\DefinitionEntry;
use App\Semantics\PartOfSpeech;

class DefinitionParser
{
    public function parse(Definition $definition): ?DefinitionAggregate
    {
        switch ($definition->source) {
            case DictionaryApi::SOURCE:
                return $this->parseDictionaryApi(
                    $definition->language(),
                    $definition->jsonData
                );
        }

        return null;
    }

    private function parseDictionaryApi(
        Language $language,
        string $jsonData
    ): ?DefinitionAggregate
    {
        $data = json_decode($jsonData, true);

        if (!is_array($data)) {
            return null;
        }

        $result = new DefinitionAggregate($language);

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

                $partOfSpeech = $this->parsePartOfSpeech(
                    $language,
                    $meaning['partOfSpeech'] ?? null
                );

                if ($partOfSpeech !== null) {
                    $defEntry->withPartOfSpeech($partOfSpeech);
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

    private function parsePartOfSpeech(Language $language, ?string $posText): ?PartOfSpeech
    {
        if (strlen($posText) === 0) {
            return null;
        }

        $posMap = [
            'ru' => [
                'мужской род' => PartOfSpeech::NOUN,
                'женский род' => PartOfSpeech::NOUN,
                'средний род' => PartOfSpeech::NOUN,
                'междометие' => PartOfSpeech::INTERJECTION,
                'частица' => PartOfSpeech::PREDICATIVE,
            ],
            'en' => [
                'noun' => PartOfSpeech::NOUN,
                'verb' => PartOfSpeech::VERB,
                'transitive verb' => PartOfSpeech::VERB,
                'adjective' => PartOfSpeech::ADJECTIVE,
            ],
        ];

        $langCode = $language->code;

        if ($langCode === null) {
            return null;
        }

        $langMap = $posMap[$langCode] ?? null;

        if ($langMap === null) {
            return null;
        }

        $posName = $langMap[mb_strtolower($posText)] ?? null;

        return PartOfSpeech::getByName($posName);
    }
}