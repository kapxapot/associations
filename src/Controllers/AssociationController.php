<?php

namespace App\Controllers;

use App\Auth\Interfaces\AuthInterface;
use App\Handlers\NotFoundHandler;
use Plasticode\Core\Interfaces\RendererInterface;
use Plasticode\Core\Response;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Http\Request as SlimRequest;

class AssociationController extends Controller
{
    private AuthInterface $auth;
    private NotFoundHandler $notFoundHandler;
    private RendererInterface $renderer;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->auth = $container->auth;
        $this->notFoundHandler = $container->notFoundHandler;
        $this->renderer = $container->renderer;
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

        if (
            is_null($association)
            || !$association->isVisibleFor($user)
        ) {
            return ($this->notFoundHandler)($request, $response);
        }

        $turnsByUser = $association->turns()->groupByUser();

        $params = $this->buildParams(
            [
                'params' => [
                    'association' => $association,
                    'turns_by_user' => $turnsByUser,
                    'disqus_id' => 'association' . $association->getId(),
                    'debug' => $debug,
                ],
            ]
        );

        return $this->render($response, 'main/associations/item.twig', $params);
    }

    public function latestChunk(
        ServerRequestInterface $request,
        ResponseInterface $response
    ) : ResponseInterface
    {
        $user = $this->auth->getUser();

        $language = $this->languageService->getCurrentLanguage($user);

        $associations = $this
            ->associationRepository
            ->getLastAddedByLanguage(
                $language,
                $this->associationConfig->associationLastAddedLimit()
            );

        $result = $this->renderer->component(
            'association_list',
            ['associations' => $associations]
        );

        return Response::text($response, $result);
    }
}
