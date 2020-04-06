<?php

namespace App\Controllers;

use App\Auth\Interfaces\AuthInterface;
use App\Handlers\NotFoundHandler;
use App\Repositories\Interfaces\AssociationRepositoryInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request as SlimRequest;

class AssociationController extends Controller
{
    private AuthInterface $auth;
    private AssociationRepositoryInterface $associationRepository;
    private NotFoundHandler $notFoundHandler;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->auth = $container->auth;
        $this->associationRepository = $container->associationRepository;
        $this->notFoundHandler = $container->notFoundHandler;
    }

    public function get(
        SlimRequest $request,
        ResponseInterface $response,
        array $args
    ) : ResponseInterface
    {
        $id = $args['id'];
        
        $debug = $request->getQueryParam('debug', null) !== null;

        $association = $this->associationRepository->get($id);
        
        $user = $this->auth->getUser();

        if (is_null($association) || !$association->isVisibleFor($user)) {
            return ($this->notFoundHandler)($request, $response);
        }

        $params = $this->buildParams(
            [
                'params' => [
                    'association' => $association,
                    'disqus_id' => 'association' . $association->getId(),
                    'debug' => $debug,
                ],
            ]
        );
        
        return $this->render($response, 'main/associations/item.twig', $params);
    }
}
