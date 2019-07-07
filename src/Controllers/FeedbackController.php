<?php

namespace App\Controllers;

use Plasticode\Core\Core;

use App\Events\AssociationFeedbackEvent;
use App\Events\WordFeedbackEvent;

class FeedbackController extends Controller
{
    public function save($request, $response, $args)
    {
        $data = $request->getParsedBody();

        $wordData = $data['word'] ?? [];
        $associationData = $data['association'] ?? [];
        
        if (empty($wordData) && empty($associationData)) {
            throw new \InvalidArgumentException('No word or association feedback provided.');
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
        
        return Core::json($response, [
            'message' => $this->translate('Feedback saved successfully.'),
        ]);
    }
}
