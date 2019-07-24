<?php

namespace App\Controllers;

use App\Events\AssociationFeedbackEvent;
use App\Events\WordFeedbackEvent;
use Plasticode\Core\Response;
use Plasticode\Exceptions\Http\BadRequestException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class FeedbackController extends Controller
{
    public function save(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
    {
        $data = $request->getParsedBody();

        $wordData = $data['word'] ?? [];
        $associationData = $data['association'] ?? [];

        if (empty($wordData) && empty($associationData)) {
            throw new BadRequestException('No word or association feedback provided.');
        }

        if (!empty($wordData)) {
            $wordFeedback = $this->wordFeedbackService
                ->toModel($wordData)
                ->save();

            $event = new WordFeedbackEvent($wordFeedback);
            $this->dispatcher->dispatch($event);
        }

        if (!empty($associationData)) {
            $assocFeedback = $this->associationFeedbackService
                ->toModel($associationData)
                ->save();

            $event = new AssociationFeedbackEvent($assocFeedback);
            $this->dispatcher->dispatch($event);
        }

        return Response::json($response, [
            'message' => $this->translate('Feedback saved successfully.'),
        ]);
    }
}
