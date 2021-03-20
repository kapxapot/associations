<?php

namespace App\Controllers;

use Plasticode\Core\Response;
use Plasticode\Exceptions\Http\BadRequestException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class WordOverrideController extends Controller
{
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response
    ) : ResponseInterface
    {
        die('not implemented');
        $data = $request->getParsedBody();

        $wordData = $data['word'] ?? [];
        $associationData = $data['association'] ?? [];

        if (empty($wordData) && empty($associationData)) {
            throw new BadRequestException(
                'No word or association feedback provided.'
            );
        }

        $user = $this->auth->getUser();

        if (!empty($wordData)) {
            $this->wordFeedbackService->save($wordData, $user);
        }

        if (!empty($associationData)) {
            $this->associationFeedbackService->save($associationData, $user);
        }

        return Response::json(
            $response,
            ['message' => $this->translate('Feedback saved successfully.')]
        );
    }
}
