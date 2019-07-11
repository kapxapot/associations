<?php

namespace App\Controllers;

use Plasticode\Collection;
use Plasticode\Exceptions\ValidationException;
use Plasticode\Util\Strings;

use App\Events\AssociationOutOfDateEvent;
use App\Events\WordFeedbackEvent;
use App\Events\WordOutOfDateEvent;
use App\Models\Association;
use App\Models\Game;
use App\Models\Word;
use App\Models\WordFeedback;

class TestController extends Controller
{
    public function index($request, $response, $args)
    {
        $this->associationUpdateTest();

        die('done');

        //return $response;
    }

    private function associationUpdateTest()
    {
        $start = microtime(true);

        $wordApprovedLimit = 10;
        $wordMatureLimit = 10;
        $assocApprovedLimit = 10;
        $assocMatureLimit = 10;

        $wordApprovedTtl = new \DateInterval('1 day');
        $wordMatureTtl = new \DateInterval('1 day');
        $assocApprovedTtl = new \DateInterval('1 day');
        $assocMatureTtl = new \DateInterval('1 day');

        $oldestApprovedWords = Word::getOldestApproved($wordApprovedTtl)
            ->limit($wordApprovedLimit)
            ->all();
        
        $oldestMatureWords = Word::getOldestMature($wordMatureTtl)
            ->limit($wordMatureLimit)
            ->all();
        
        $oldestApprovedAssocs = Association::getOldestApproved($assocApprovedTtl)
            ->limit($assocApprovedLimit)
            ->all();
        
        $oldestMatureAssocs = Association::getOldestApproved($assocMatureTtl)
            ->limit($assocMatureLimit)
            ->all();

        foreach ($oldestApprovedWords as $word) {
            $event = new WordOutOfDateEvent($word);
            $this->dispatcher->dispatch($event);
        }

        foreach ($oldestMatureWords as $word) {
            $event = new WordOutOfDateEvent($word);
            $this->dispatcher->dispatch($event);
        }

        foreach ($oldestApprovedAssocs as $assoc) {
            $event = new AssociationOutOfDateEvent($assoc);
            $this->dispatcher->dispatch($event);
        }

        foreach ($oldestMatureAssocs as $assoc) {
            $event = new AssociationOutOfDateEvent($assoc);
            $this->dispatcher->dispatch($event);
        }
        
        $end = microtime(true);

        var_dump([$end - $start]);
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

    private function runFeedbackTests()
    {
        $results = [
            'empty word array' => $this->wordFeedbackEmptyTest(),
            'full word array' => $this->wordFeedbackFullTest(),
            'empty association array' => $this->associationFeedbackEmptyTest(),
            'full association array' => $this->associationFeedbackFullTest(),
        ];
        
        var_dump($results);
    }
    
    /*

    {
        word: {
            id: 194,
            dislike: false,
            withTypo: false,
            typo: null,
            withDuplicate: false,
            duplicate: null,
            mature: false
        },
        association: {
            id: 224,
            dislike: false,
            mature: false
        }
    }
            
    */
    
    private function associationFeedbackFullTest()
    {
        $data = [
            'association_id' => '986',
            'dislike' => 'true',
            'mature' => 'true',
        ];
        
        try {
            $model = $this->associationFeedbackService->toModel($data);
        }
        catch (ValidationException $ex) {
            return false;
        }

        return true;
    }
    
    private function associationFeedbackEmptyTest()
    {
        $data = [
        ];
        
        try {
            $model = $this->associationFeedbackService->toModel($data);
        }
        catch (ValidationException $ex) {
            return !empty($ex->errors['association_id']);
        }

        return false;
    }
    
    private function wordFeedbackFullTest()
    {
        $data = [
            'word_id' => '194',
            'dislike' => 'true',
            'typo' => 'ababa',
            'duplicate' => 'скрип',
            'mature' => 'false',
        ];
        
        try {
            $model = $this->wordFeedbackService->toModel($data);
        }
        catch (ValidationException $ex) {
            return false;
        }

        return true;
    }
    
    private function wordFeedbackEmptyTest()
    {
        $data = [
        ];
        
        try {
            $model = $this->wordFeedbackService->toModel($data);
        }
        catch (ValidationException $ex) {
            return !empty($ex->errors['word_id']);
        }

        return false;
    }
    
    private function randomWordTest()
    {
        $start = microtime(true);
        
        $user = $this->auth->getUser();
        $language = $this->languageService->getDefaultLanguage();
        
        $word = $this->languageService->getRandomWordForUser($language, $user);
        
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
    
    private function collectionFlattenTest()
    {
        $coll = Collection::make([
            'element',
            Collection::make(['one', 'two']),
            'another',
            1,
            [1, 2, 'hi'],
            'the end',
        ]);
        
        return [$coll, $coll->flatten()];
    }
}
