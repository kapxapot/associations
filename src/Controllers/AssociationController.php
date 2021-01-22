<?php

namespace App\Controllers;

use Plasticode\Core\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Http\Request;

class AssociationController extends Controller
{
    public function get(
        Request $request,
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

        $language = $this->languageService->getCurrentLanguageFor($user);

        $associations = $this
            ->associationRepository
            ->getLastAddedByLanguage(
                $language,
                $this->associationConfig->associationLastAddedLimit()
            );

        $result = $associations->any()
            ? $this->renderer->component(
                'association_list',
                ['associations' => $associations]
            )
            : $this->translate('No associations yet. :(');

        return Response::text($response, $result);
    }
}
