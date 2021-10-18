<?php

namespace App\Controllers;

use App\Repositories\Interfaces\AssociationRepositoryInterface;
use App\Services\AssociationRecountService;
use Plasticode\Core\Response;
use Plasticode\Handlers\Interfaces\NotFoundHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AssociationRecountController
{
    private AssociationRepositoryInterface $associationRepository;
    private AssociationRecountService $associationRecountService;

    private NotFoundHandlerInterface $notFoundHandler;

    public function __construct(
        AssociationRepositoryInterface $associationRepository,
        AssociationRecountService $associationRecountService,
        NotFoundHandlerInterface $notFoundHandler
    )
    {
        $this->associationRepository = $associationRepository;
        $this->associationRecountService = $associationRecountService;

        $this->notFoundHandler = $notFoundHandler;
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface
    {
        $id = $args['id'];

        $association = $this->associationRepository->get($id);

        if ($association === null) {
            return ($this->notFoundHandler)($request, $response);
        }

        $association = $this->associationRecountService->recountAll($association);

        return Response::json($response, $association->serialize());
    }
}
