<?php

namespace App\Controllers;

use App\Models\Association;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AssociationController extends Controller
{
    public function get(ServerRequestInterface $request, ResponseInterface $response, array $args) : ResponseInterface
    {
        $id = $args['id'];
        
        $debug = $request->getQueryParam('debug', null) !== null;

        $association = Association::get($id);
        
        $user = $this->auth->getUser();

        if ($association === null || !$association->isVisibleForUser($user)) {
            return $this->notFound($request, $response);
        }

        $params = $this->buildParams([
            'params' => [
                'association' => $association,
                'disqus_id' => 'association' . $association->getId(),
                'debug' => $debug,
            ],
        ]);
        
        return $this->render($response, 'main/associations/item.twig', $params);
    }
}
