<?php

namespace App\Controllers;

use Plasticode\Core\Response;
use Plasticode\Handlers\Interfaces\NotFoundHandlerInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AssociationController extends Controller
{
    private NotFoundHandlerInterface $notFoundHandler;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->notFoundHandler = $container->get(NotFoundHandlerInterface::class);
    }

    public function get(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ) : ResponseInterface
    {
        $id = $args['id'];

        $debug = $this->isDebug($request);

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

        $language = $this->languageService->getCurrentLanguageFor($user);

        $associations = $this
            ->associationRepository
            ->getLastAddedByLanguage(
                $language,
                $this->associationConfig->associationLastAddedLimit()
            );

        return $associations->any()
            ? $this->render(
                $response,
                'components/association_list.twig',
                $this->buildParams(
                    [
                        'params' => [
                            'associations' => $associations,
                        ],
                    ]
                )
            )
            : Response::text($response, $this->translate('No associations yet. :('));
    }
}
