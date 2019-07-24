<?php

namespace App\Controllers;

use App\Models\Word;
use Plasticode\Core\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class WordController extends Controller
{
    public function index(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
    {
        $debug = $request->getQueryParam('debug', null) !== null;

        $params = $this->buildParams([
            'params' => [
                'title' => 'Слова',
                'debug' => $debug,
            ],
        ]);
        
        return $this->render($response, 'main/words/index.twig', $params);
    }
    
    public function publicWords(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
    {
        $words = Word::getPublic()
            ->all()
            ->map(function ($word) {
                return $word->serialize();
            });

        return Response::json($response, $words, ['params' => $request->getQueryParams()]);
    }
    
    public function get(ServerRequestInterface $request, ResponseInterface $response, array $args) : ResponseInterface
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
                'word' => $word,
                'disqus_id' => 'word' . $word->getId(),
                'debug' => $debug,
            ],
        ]);
        
        return $this->render($response, 'main/words/item.twig', $params);
    }
}
