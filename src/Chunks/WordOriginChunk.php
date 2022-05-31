<?php

namespace App\Chunks;

use App\Auth\Auth;
use App\Chunks\Core\ChunkResult;
use App\Chunks\Core\Interfaces\ChunkInterface;
use App\Repositories\Interfaces\WordRepositoryInterface;
use Plasticode\Exceptions\Http\NotFoundException;

/**
 * @throws NotFoundException
 */
class WordOriginChunk implements ChunkInterface
{
    private WordRepositoryInterface $wordRepository;
    private Auth $auth;

    public function __construct(
        WordRepositoryInterface $wordRepository,
        Auth $auth
    )
    {
        $this->wordRepository = $wordRepository;
        $this->auth = $auth;
    }

    public function process(array $params): ChunkResult
    {
        $wordId = $params['id'] ?? null;

        $word = $this->wordRepository->get($wordId);
        $user = $this->auth->getUser();

        if (!$word || !$word->isVisibleFor($user)) {
            throw new NotFoundException('Word not found.');
        }

        return new ChunkResult(
            'word_origin',
            [
                'word' => $word,
            ]
        );
    }
}
