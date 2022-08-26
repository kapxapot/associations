<?php

namespace App\Tests\Models\Word;

use App\Collections\WordOverrideCollection;
use App\Collections\WordRelationCollection;
use App\Models\Language;
use App\Models\Word;
use App\Models\WordRelation;
use App\Models\WordRelationType;
use App\Models\YandexDictWord;
use App\Semantics\PartOfSpeech;
use App\Testing\Factories\WordRepositoryFactory;
use PHPUnit\Framework\TestCase;

final class WordPartsOfSpeechTest extends TestCase
{
    public function testCycledWordFormRelationsPartsOfSpeech(): void
    {
        $wordRepository = WordRepositoryFactory::make();

        $relationType = new WordRelationType();
        $relationType->wordForm = 1;

        $this->language = new Language(['id' => 1]);

        // [1] word1 (noun) -word-form-> word2
        // [2] word2 (verb) -word-form/main-> word1

        // expected:
        // word1 (noun, verb)
        // word2 (noun, verb)
        // no endless cycles

        $word1 = (new Word([
            'language_id' => $this->language->getId(),
            'word' => 'word1',
        ]))
            ->withLanguage($this->language)
            ->withMain(null)
            ->withOverrides(WordOverrideCollection::empty())
            ->withParsedDefinition(null);

        $word2 = (new Word([
            'language_id' => $this->language->getId(),
            'word' => 'word2',
        ]))
            ->withLanguage($this->language)
            ->withMain($word1)
            ->withOverrides(WordOverrideCollection::empty())
            ->withParsedDefinition(null);

        $word1 = $wordRepository->save($word1);
        $word2 = $wordRepository->save($word2);

        // add relations

        // word1
        $relation12 = (new WordRelation([
            'word_id' => $word1->getId(),
            'main_word_id' => $word2->getId(),
        ]))
            ->withType($relationType)
            ->withWord($word1)
            ->withMainWord($word2);

        $word1->withRelations(
            WordRelationCollection::collect($relation12)
        );

        // word2
        $relation21 = (new WordRelation([
            'word_id' => $word2->getId(),
            'main_word_id' => $word1->getId(),
            'primary' => 1,
        ]))
            ->withType($relationType)
            ->withWord($word2)
            ->withMainWord($word1);

        $word2->withRelations(
            WordRelationCollection::collect($relation21)
        );

        // add part of speech dict words

        $word1->withDictWord(new YandexDictWord([
            'pos' => 'noun',
        ]));

        $word2->withDictWord(new YandexDictWord([
            'pos' => 'verb',
        ]));

        // test
        $pos1 = $word1->partsOfSpeech();
        $this->assertCount(2, $pos1);
        $this->assertEquals(PartOfSpeech::NOUN, $pos1[0]->name());
        $this->assertEquals(PartOfSpeech::VERB, $pos1[1]->name());

        $pos2 = $word1->partsOfSpeech();
        $this->assertCount(2, $pos2);
        $this->assertEquals(PartOfSpeech::NOUN, $pos2[0]->name());
        $this->assertEquals(PartOfSpeech::VERB, $pos2[1]->name());
    }
}
