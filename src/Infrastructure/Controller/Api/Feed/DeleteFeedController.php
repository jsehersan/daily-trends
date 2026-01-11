<?php

namespace App\Infrastructure\Controller\Api\Feed;

use App\Application\UseCase\Feed\DeleteFeed;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

class DeleteFeedController extends AbstractController
{
    public function __construct(
        private readonly DeleteFeed $useCase
    ) {
    }

    #[Route(path: '{id}', name: 'api_feeds_delete', methods: ['DELETE'])]
    public function __invoke(Uuid $id): JsonResponse
    {

        $this->useCase->execute((string) $id);

        //Si todo ha ido bien devolvemos un 204
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}