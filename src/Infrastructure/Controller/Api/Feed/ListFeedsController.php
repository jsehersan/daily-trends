<?php

namespace App\Infrastructure\Controller\Api\Feed;

use App\Application\DTO\Request\Feed\ListFeedsRequest;
use App\Application\UseCase\Feed\ListFeeds;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

class ListFeedsController extends AbstractController
{
    public function __construct(
        private readonly ListFeeds $useCase
    ) {
    }

    // La ruta es '' porque en routes.yaml ya definimos el prefijo /feeds
    #[Route('', name: 'api_feeds_list', methods: ['GET'])]
    public function __invoke(
        #[MapQueryString] ?ListFeedsRequest $request = null
    ): JsonResponse {


        $request ??= new ListFeedsRequest();

        $result = $this->useCase->execute(
            $request->page,
            $request->limit,
            $request->sortBy,
            $request->sortOrder
        );

        return $this->json($result);
    }
}