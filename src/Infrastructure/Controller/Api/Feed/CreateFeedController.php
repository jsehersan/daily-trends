<?php

namespace App\Infrastructure\Controller\Api\Feed;

use App\Application\DTO\Request\Feed\CreateFeedRequest;
use App\Application\UseCase\Feed\CreateFeed;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

class CreateFeedController extends AbstractController
{
    public function __construct(
        private readonly CreateFeed $useCase
    ) {
    }

    #[Route(path: '', name: 'api_feeds_create', methods: ['POST'])]
    public function __invoke(
        #[MapRequestPayload] CreateFeedRequest $request
    ): JsonResponse {

        $response = $this->useCase->execute($request);

        return $this->json($response, Response::HTTP_CREATED);
    }
}