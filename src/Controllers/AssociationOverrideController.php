<?php

namespace App\Controllers;

use App\Services\AssociationOverrideService;
use Plasticode\Core\Response;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AssociationOverrideController extends Controller
{
    private AssociationOverrideService $associationOverrideService;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->associationOverrideService =
            $container->get(AssociationOverrideService::class);
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response
    ) : ResponseInterface
    {
        $data = $request->getParsedBody();
        $user = $this->auth->getUser();

        $this->associationOverrideService->save($data, $user);

        return Response::json(
            $response,
            ['message' => $this->translate('Association override saved successfully.')]
        );
    }
}
