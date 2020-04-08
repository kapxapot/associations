<?php

namespace App\Controllers;

use App\Events\WordFeedbackEvent;
use App\Models\Association;
use App\Models\Game;
use App\Models\Word;
use App\Models\WordFeedback;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class TestController extends Controller
{
    public function index(ServerRequestInterface $request, ResponseInterface $response)
    {
        die('done');

        //return $response;
    }

    private function hasPlayerTest()
    {
        $game = Game::get(43);
        $user = $this->userRepository->get(3);

        var_dump($game->hasPlayer($user));
    }

    private function invisibleCountTest()
    {
        $word = Word::get(2);
        var_dump(['approved', $word->approvedInvisibleAssociationsStr()]);
        var_dump(['unapproved', $word->unapprovedInvisibleAssociationsStr()]);
    }

    private function eventTest()
    {
        $wordFeedback = WordFeedback::get(2);
        $event = new WordFeedbackEvent($wordFeedback);

        $this->dispatcher->dispatch($event);
    }

    private function eventLogTest()
    {
        $this->eventLog->info('Test');
    }
    
    private function randomWordTest()
    {
        $start = microtime(true);
        
        $user = $this->auth->getUser();
        $language = $this->languageService->getDefaultLanguage();
        
        $word = $this->languageService->getRandomWordFor($user, $language);
        
        $end = microtime(true);
        
        return [$word->id, $word->word, $word->creator()->displayName(), $end - $start];
    }
    
    private function wordsApprovedTest()
    {
        $words = Word::getAll();
        $wordsCount = $words->count();
        $approvedCount = $words->where(function ($w) {
            return $w->isApproved();
        })
        ->count();
        
        $approvedByAssoc = Association::getApproved()
            ->map(function ($assoc) {
                return $assoc->words();
            })
            ->flatten()
            ->distinct();
        
        $approvedByAssocCount = $approvedByAssoc->count();
        
        var_dump($wordsCount, $approvedCount, $approvedByAssocCount);
        
        return $approvedByAssoc->extract('word');
    }
}
