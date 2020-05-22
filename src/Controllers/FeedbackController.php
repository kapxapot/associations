<?php

namespace App\Controllers;

use App\Auth\Interfaces\AuthInterface;
use App\Events\AssociationFeedbackEvent;
use App\Events\WordFeedbackEvent;
use App\Repositories\Interfaces\AssociationFeedbackRepositoryInterface;
use App\Repositories\Interfaces\WordFeedbackRepositoryInterface;
use App\Services\AssociationFeedbackService;
use App\Services\WordFeedbackService;
use Plasticode\Core\Response;
use Plasticode\Events\EventDispatcher;
use Plasticode\Exceptions\Http\BadRequestException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class FeedbackController extends Controller
{
    private AssociationFeedbackRepositoryInterface $associationFeedbackRepository;
    private WordFeedbackRepositoryInterface $wordFeedbackRepository;

    private AuthInterface $auth;
    private AssociationFeedbackService $associationFeedbackService;
    private EventDispatcher $eventDispatcher;
    private WordFeedbackService $wordFeedbackService;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->associationFeedbackRepository =
            $container->associationFeedbackRepository;

        $this->wordFeedbackRepository = $container->wordFeedbackRepository;

        $this->auth = $container->auth;

        $this->associationFeedbackService =
            $container->associationFeedbackService;

        $this->eventDispatcher = $container->eventDispatcher;
        $this->wordFeedbackService = $container->wordFeedbackService;
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

            $event = new WordFeedbackEvent($wordFeedback);
            $this->eventDispatcher->dispatch($event);
        }

        if (!empty($associationData)) {
            $assocFeedback = $this
                ->associationFeedbackService
                ->toModel($associationData, $user);

            $assocFeedback = $this
                ->associationFeedbackRepository
                ->save($assocFeedback);

            $event = new AssociationFeedbackEvent($assocFeedback);
            $this->eventDispatcher->dispatch($event);
        }

        return Response::json(
            $response,
            ['message' => $this->translate('Feedback saved successfully.')]
        );
    }
}
