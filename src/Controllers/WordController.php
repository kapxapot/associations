<?php

namespace App\Controllers;

use Plasticode\Core\Core;

use App\Models\Word;

class WordController extends Controller
{
    public function index($request, $response, $args)
    {
        $debug = $request->getQueryParam('debug', null) !== null;

        $words = Word::getAll();

        $params = $this->buildParams([
            'params' => [
                'title' => 'Слова',
                'words' => $words,
                'debug' => $debug,
            ],
        ]);
        
        return $this->view->render($response, 'main/words/index.twig', $params);
    }
    
    public function publicWords($request, $response, $args)
    {
        $limit = $request->getQueryParam('limit', 0);
        
        $words = Word::query()
            ->limit($limit)
            ->all()
            ->where(function ($word) {
                return !$word->isMature();
            })
            ->map(function ($word) {
                return $word->serialize();
            });

        return Core::json($response, $words, ['params' => $request->getQueryParams()]);
    }
    
    public function item($request, $response, $args)
    {
        $id = $args['id'];
        
        $debug = $request->getQueryParam('debug', null) !== null;

        $word = Word::get($id);
        
        $user = $this->auth->getUser();

        if ($word === null || !$word->isVisibleForUser($user)) {
            return $this->notFound($request, $response);
        }

        $params = $this->buildParams([
            'params' => [
                'title' => mb_strtoupper($word->word) . ' - Слова',
                'word' => $word,
                'disqus_id' => 'word' . $word->getId(),
                'debug' => $debug,
            ],
        ]);
        
        return $this->view->render($response, 'main/words/item.twig', $params);
    }
}
