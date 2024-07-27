<?php

namespace Brightwood\Controllers;

use Brightwood\Services\StoryService;
use Exception;
use Plasticode\Core\Response;
use Plasticode\Exceptions\Http\HttpException;
use Plasticode\Exceptions\Http\NotFoundException;
use Plasticode\Util\Debug;
use Plasticode\Util\Text;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Webmozart\Assert\Assert;

class StoryController
{
    private LoggerInterface $logger;
    private StoryService $storyService;

    public function __construct(
        LoggerInterface $logger,
        StoryService $storyService
    )
    {
        $this->logger = $logger;
        $this->storyService = $storyService;
    }

    public function get(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface
    {
        $uuid = $args['uuid'];

        try {
            Assert::notEmpty($uuid);

            $story = $this->storyService->getStoryByUuid($uuid);

            if (!$story) {
                throw new NotFoundException('Story not found.');
            }

            $currentVersion = $story->currentVersion();

            if (!$currentVersion) {
                $this->logger->error("Story [id = {$story->getId()}, uuid = {$uuid}] doesn't have a version.");

                throw new NotFoundException('Story version not found.');
            }
    
            return Response::json(
                $response,
                json_decode($story->currentVersion()->jsonData, true)
            );
        } catch (HttpException $ex) {
            return Response::json(
                $response,
                [
                    'error' => true,
                    'statusCode' => $ex->getErrorCode(),
                    'message' => $ex->getMessage(),
                ]
            )
            ->withStatus($ex->getErrorCode());
        } catch (Exception $ex) {
            $this->logger->error($ex->getMessage());

            $lines = Debug::exceptionTrace($ex);
            $this->logger->info(Text::join($lines));

            $statusCode = 500;

            return Response::json(
                $response,
                [
                    'error' => true,
                    'statusCode' => $statusCode,
                    'message' => 'Server error.',
                ]
            )
            ->withStatus($statusCode);
        }
    }
}
