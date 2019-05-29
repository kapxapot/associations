<?php

namespace App\Controllers;

use App\Models\Association;

class AssociationController extends Controller
{
	public function item($request, $response, $args)
	{
		$id = $args['id'];
		
	    $debug = $request->getQueryParam('debug', null) !== null;

		$association = Association::get($id);

		if ($association === null) {
			return $this->notFound($request, $response);
		}
		
		$userIds = array_keys($association->turnsByUsers());
		
		$users = [];
		
		foreach ($userIds as $id) {
		    $users[$id] = $this->userRepository->get($id);
		}

	    $params = $this->buildParams([
	        'params' => [
    	        'title' => mb_strtoupper($association->firstWord()->word . ' → ' . $association->secondWord()->word),
    	        'association' => $association,
    	        'association_users' => $users,
    			'disqus_id' => 'association' . $association->getId(),
    	        'debug' => $debug,
            ],
        ]);
	    
		return $this->view->render($response, 'main/associations/item.twig', $params);
	}
}
