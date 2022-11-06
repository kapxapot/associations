<?php

namespace App\Data;

use App\Semantics\KeyboardTranslator;
use Plasticode\Data\Query;
use Plasticode\Util\Strings;

/**
 * Enhanced searcher that creates a more sophisticated search over {@see Query}.
 */
class MultilingualSearcher
{
    private KeyboardTranslator $translator;

    public function __construct(
        KeyboardTranslator $translator
    )
    {
        $this->translator = $translator;
    }

    /**
     * Breaks the search string into words and applies `where()` with each of them
     * using AND.
     *
     * @param string $langCode The main language code of search.
     * @param string $searchStr One or several words.
     * @param integer $paramCount How many times every word must be passed to `where()`.
     */
    public function search(
        string $langCode,
        Query $query,
        string $searchStr,
        string $where,
        int $paramCount = 1
    ): Query
    {
        // so the idea here is as follows
        // 1. when we get a search term like 'ghbdtn', we need to search both
        //    by it and by its translated variant 'привет'
        // 2. so if the incoming $where string is like '(word like ? or
        //    user.login like ? or user.name like ?)' with [q, q, q] params,
        //    it should be transformed into '(word like ? or user.login like ?
        //    or user.name like ?) or (word like ? or user.login like ? or user.name like ?)'
        //    with [q, q, q, tq, tq, tq] params
        $translationDirection = $this->translator->getTranslationDirection($langCode);

        // if there is no translation direction for this language
        // do ordinary search
        if ($translationDirection === null) {
            return $query->search($searchStr, $where, $paramCount);
        }

        // do multilanguage search
        $combinedWhere = $where . ' or ' . $where;

        $words = Strings::toWords($searchStr);

        foreach ($words as $word) {
            $wrapped = '%' . $word . '%';
            $translated = '%' . $this->translator->translate($translationDirection, $word) . '%';

            $params = array_merge(
                array_fill(0, $paramCount, $wrapped),
                array_fill(0, $paramCount, $translated),
            );

            $query = $query->whereRaw($combinedWhere, $params);
        }

        return $query;
    }
}
