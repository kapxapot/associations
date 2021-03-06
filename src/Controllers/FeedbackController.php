<?php

namespace App\Controllers;

use App\Services\AssociationFeedbackService;
use App\Services\WordFeedbackService;
use Plasticode\Core\Response;
use Plasticode\Exceptions\Http\BadRequestException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class FeedbackController extends Controller
{
    private AssociationFeedbackService $associationFeedbackService;
    private WordFeedbackService $wordFeedbackService;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->associationFeedbackService =
            $container->get(AssociationFeedbackService::class);

        $this->wordFeedbackService = $container->get(WordFeedbackService::class);
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response
    ) : ResponseInterface
    {
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
