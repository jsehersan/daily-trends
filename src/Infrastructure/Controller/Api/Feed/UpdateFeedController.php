<?php

namespace App\Infrastructure\Controller\Api\Feed;

use App\Application\DTO\Request\Feed\UpdateFeedRequest;
use App\Application\UseCase\Feed\UpdateFeed;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

class UpdateFeedController extends AbstractController
{
    public function __construct(
        private readonly UpdateFeed $useCase
    ) {
    }

    #[Route(path: '{id}', name: 'api_feeds_update', methods: ['PUT'])]
    public function __invoke(
        Uuid $id,
        #[MapRequestPayload] UpdateFeedRequest $request
    ): JsonResponse {

        $response = $this->useCase->execute((string) $id, $request);

        return $this->json($response);
    }
}