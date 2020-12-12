<?php

namespace App\Controllers;

use App\Collections\WordCollection;
use App\Models\Association;
use App\Models\Word;
use App\Services\WordService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @deprecated
 */
class TestController extends Controller
{
    public function index(
        ServerRequestInterface $request,
        ResponseInterface $response
    )
    {
        die('done');
    }

    private function hasPlayerTest()
    {
        $game = $this->gameRepository->get(43);
        $user = $this->userRepository->get(3);

        var_dump($game->hasPlayer($user));
    }

    private function invisibleCountTest()
    {
        $word = $this->wordRepository->get(2);

        /** @var WordService */
        $wordService = $this->wordService;

        var_dump(
            [
                'approved',
                $wordService->approvedInvisibleAssociationsStr($word)
            ]
        );

        var_dump(
            [
                'unapproved',
                $wordService->notApprovedInvisibleAssociationsStr($word)
            ]
        );
    }

    private function randomWordTest()
    {
        $start = microtime(true);
        
        $user = $this->auth->getUser();
        $language = $this->languageService->getDefaultLanguage();
        
        $word = $this->languageService->getRandomWordFor($user, $language);
        
        $end = microtime(true);
        
        return [
            $word->getId(),
            $word->word,
            $word->creator()->displayName(),
            $end - $start
        ];
    }

    private function wordsApprovedTest()
    {
        $language = $this->languageService->getDefaultLanguage();
        $words = $this->wordRepository->getAllByLanguage($language);
        $wordsCount = $words->count();

        $approvedCount = $words
            ->where(
                fn (Word $w) => $w->isApproved()
            )
            ->count();

        $approvedByAssoc =
            WordCollection::from(
                $this
                    ->associationRepository
                    ->getAllApproved($language)
                    ->flatMap(
                        fn (Association $assoc) => $assoc->words()
                    )
            )
            ->distinct();

        $approvedByAssocCount = $approvedByAssoc->count();

        var_dump($wordsCount, $approvedCount, $approvedByAssocCount);

        return $approvedByAssoc->extract('word');
    }
}
