<?php

namespace App\Controllers;

use Plasticode\Collection;
use Plasticode\Exceptions\ValidationException;

use App\Models\Association;
use App\Models\Game;
use App\Models\Word;

class TestController extends Controller
{
    public function index($request, $response, $args)
    {
        $results = [
            'empty word array' => $this->wordFeedbackEmptyTest(),
            'full word array' => $this->wordFeedbackFullTest(),
            'empty association array' => $this->associationFeedbackEmptyTest(),
            'full association array' => $this->associationFeedbackFullTest(),
        ];
        
        dd($results);

        //return $response;
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
        
        dd($word->id, $word->word, $word->creator()->displayName(), $end - $start);
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
        
        dd($approvedByAssoc->extract('word'));
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
        
        var_dump($coll);
        
        dd($coll->flatten());
    }
}
