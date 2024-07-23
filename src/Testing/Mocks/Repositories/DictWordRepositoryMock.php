<?php

namespace App\Testing\Mocks\Repositories;

use App\Collections\DictWordCollection;
use App\Models\Interfaces\DictWordInterface;
use App\Models\Language;
use App\Models\Word;
use App\Models\YandexDictWord;
use App\Repositories\Interfaces\DictWordRepositoryInterface;
use App\Repositories\Interfaces\LanguageRepositoryInterface;
use Plasticode\Testing\Mocks\Repositories\Generic\RepositoryMock;

class DictWordRepositoryMock extends RepositoryMock implements DictWordRepositoryInterface
{
    private DictWordCollection $dictWords;

    private LanguageRepositoryInterface $languageRepository;

    public function __construct(
        LanguageRepositoryInterface $languageRepository
    )
    {
        $this->dictWords = DictWordCollection::empty();

        $this->languageRepository = $languageRepository;
    }

    public function getCountByLanguage(Language $language): int
    {
        return $this
            ->dictWords
            ->where(
                fn (DictWordInterface $dw) => $dw->getLanguage()->equals($language)
            )
            ->count();
    }

    public function store(array $data): DictWordInterface
    {
        /** @var YandexDictWord */
        $dictWord = YandexDictWord::create($data);

        return $dictWord
            ->withLanguage(
                $this->languageRepository->get($dictWord->languageId)
            )
            ->withLinkedWord(null);
    }

    public function save(DictWordInterface $dictWord): DictWordInterface
    {
        $this->dictWords = $this->dictWords->add($dictWord);

        return $dictWord;
    }

    public function getByWord(Word $word): ?DictWordInterface
    {
        return $this
            ->dictWords
            ->first(
                fn (DictWordInterface $dw) => $word->equals($dw->getLinkedWord())
            );
    }

    public function getByWordStr(Language $language, string $wordStr): ?DictWordInterface
    {
        return $this
            ->dictWords
            ->first(
                fn (DictWordInterface $dw) =>
                $dw->getLanguage()->equals($language)
                && $dw->getWord() == $wordStr
            );
    }

    /**
     * Returns dict words without associated words that need to be updated.
     *
     * @param integer $ttlMin Update time-to-live in minutes.
     */
    public function getAllDanglingOutOfDate(
        int $ttlMin,
        int $limit = 0
    ): DictWordCollection
    {
        // placeholder
        return DictWordCollection::empty();
    }
}
