<?php

namespace App\Controllers;

use Plasticode\Core\Core;

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
            $this->wordFeedbackService
                ->toModel($wordData)
                ->save();
        }
        
        if (!empty($associationData)) {
            $this->associationFeedbackService
                ->toModel($associationData)
                ->save();
        }
        
        return Core::json($response, [
            'message' => $this->translate('Feedback saved successfully.'),
        ]);
    }
}
