<?php

namespace App\Controllers;

use App\Events\Feedback\AssociationFeedbackCreatedEvent;
use App\Events\Feedback\WordFeedbackCreatedEvent;
use App\Repositories\Interfaces\AssociationFeedbackRepositoryInterface;
use App\Repositories\Interfaces\WordFeedbackRepositoryInterface;
use App\Services\AssociationFeedbackService;
use App\Services\WordFeedbackService;
use Plasticode\Core\Response;
use Plasticode\Exceptions\Http\BadRequestException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class FeedbackController extends Controller
{
    private AssociationFeedbackRepositoryInterface $associationFeedbackRepository;
    private WordFeedbackRepositoryInterface $wordFeedbackRepository;

    private AssociationFeedbackService $associationFeedbackService;
    private WordFeedbackService $wordFeedbackService;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->associationFeedbackRepository =
            $container->get(AssociationFeedbackRepositoryInterface::class);

        $this->wordFeedbackRepository =
            $container->get(WordFeedbackRepositoryInterface::class);

        $this->associationFeedbackService =
            $container->get(AssociationFeedbackService::class);

        $this->wordFeedbackService = $container->get(WordFeedbackService::class);
    }

    public function save(
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
            $wordFeedback = $this
                ->wordFeedbackService
                ->toModel($wordData, $user);

            $wordFeedback = $this
                ->wordFeedbackRepository
                ->save($wordFeedback);

            $event = new WordFeedbackCreatedEvent($wordFeedback);
            $this->eventDispatcher->dispatch($event);
        }

        if (!empty($associationData)) {
            $assocFeedback = $this
                ->associationFeedbackService
                ->toModel($associationData, $user);

            $assocFeedback = $this
                ->associationFeedbackRepository
                ->save($assocFeedback);

            $event = new AssociationFeedbackCreatedEvent($assocFeedback);
            $this->eventDispatcher->dispatch($event);
        }

        return Response::json(
            $response,
            ['message' => $this->translate('Feedback saved successfully.')]
        );
    }
}
