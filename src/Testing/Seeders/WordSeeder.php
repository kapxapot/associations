<?php

namespace App\Testing\Seeders;

use App\Collections\WordFeedbackCollection;
use App\Models\Word;
use App\Repositories\Interfaces\LanguageRepositoryInterface;
use Plasticode\Testing\Seeders\Interfaces\ArraySeederInterface;

class WordSeeder implements ArraySeederInterface
{
    private LanguageRepositoryInterface $languageRepository;

    public function __construct(
        LanguageRepositoryInterface $languageRepository
    )
    {
        $this->languageRepository = $languageRepository;
    }

    /**
     * @return Word[]
     */
    public function seed() : array
    {
        $words = [
            new Word(
                [
                    'id' => 1,
                    'language_id' => 1,
                    'word' => 'стол',
                    'approved' => 1,
                ]
            ),
            new Word(
                [
                    'id' => 2,
                    'language_id' => 1,
                    'word' => 'табурет',
                ]
            ),
            new Word(
                [
                    'id' => 3,
                    'language_id' => 1,
                    'word' => 'кровать',
                ]
            ),
        ];

        return array_map(
            fn (Word $w) => $w
                ->withLanguage(
                    $this->languageRepository->get($w->languageId)
                )->withFeedbacks(
                    WordFeedbackCollection::empty()
                ),
            $words
        );
    }
}
