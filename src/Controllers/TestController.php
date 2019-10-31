<?php

namespace App\Controllers;

use App\Events\WordFeedbackEvent;
use App\Models\Association;
use App\Models\Game;
use App\Models\Language;
use App\Models\Word;
use App\Models\WordFeedback;
use Plasticode\Collection;
use Plasticode\Exceptions\ValidationException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class TestController extends Controller
{
    public function index(ServerRequestInterface $request, ResponseInterface $response)
    {
        $this->dictionaryWordStrTest('чучундрик');
        $this->dictionaryWordStrTest('самолет');
        $this->dictionaryWordStrTest('таблица');
        $this->dictionaryWordTest(1);

        die('done');

        //return $response;
    }

    private function dictionaryWordStrTest(string $wordStr)
    {
        $language = Language::get(Language::RUSSIAN);
        var_dump($wordStr);
        var_dump($this->dictionaryService->isWordStrKnown($language, $wordStr));
    }

    private function dictionaryWordTest(int $id)
    {
        $word = Word::get($id);
        var_dump($word->word);
        var_dump($this->dictionaryService->isWordKnown($word));
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
}
