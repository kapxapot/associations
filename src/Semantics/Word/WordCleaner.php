<?php

namespace App\Semantics\Word;

use App\Models\Game;
use App\Models\Language;
use App\Services\LanguageService;
use Plasticode\Util\Arrays;

class WordCleaner
{
    private LanguageService $languageService;
    private Tokenizer $tokenizer;

    public function __construct(
        LanguageService $languageService,
        Tokenizer $tokenizer
    )
    {
        $this->languageService = $languageService;
        $this->tokenizer = $tokenizer;
    }

    /**
     * Purges the prev word and deduplicates the word.
     */
    public function clean(string $wordStr, Game $game): string
    {
        $prevWord = $game->lastTurnWord();

        if ($prevWord !== null) {
            $wordStr = $this->purge($wordStr, $prevWord->word);
        }

        $wordStr = $this->deduplicate($wordStr, $game->language());

        return $wordStr;
    }

    /**
     * Removes the previous word from the word if it's contained there.
     *
     * If the word consists of several tokens, it is removed only if ALL tokens
     * are present in the new word.
     */
    public function purge(string $word, string $prevWord): string
    {
        $tokens = $this->tokenizer->tokenize($word);
        $prevWordTokens = $this->tokenizer->tokenize($prevWord);

        // check that all prev tokens are present in new word
        if (!Arrays::contains($tokens, $prevWordTokens)) {
            return $word;
        }

        // todo: extract this into Arrays::subtract
        $filteredTokens = array_filter(
            $tokens,
            fn (string $token) => !in_array($token, $prevWordTokens)
        );

        if (empty($filteredTokens)) {
            return $word;
        }

        return $this->tokenizer->join($filteredTokens);
    }

    /**
     * Converts 'word word' to 'word' for known words.
     */
    public function deduplicate(string $wordStr, Language $language): string
    {
        $tokens = $this->tokenizer->tokenize($wordStr);

        $originalCount = count($tokens);

        if ($originalCount <= 1) {
            return $wordStr;
        }

        $deduplicatedTokens = array_unique($tokens);

        if (count($deduplicatedTokens) !== 1) {
            return $wordStr;
        }

        $deduplicatedCandidate = $deduplicatedTokens[0];

        $word = $this->languageService->findWord($language, $wordStr)
            ?? $this->languageService->findWord($language, $deduplicatedCandidate);

        return $word !== null
            ? $word->word
            : $wordStr;
    }
}
