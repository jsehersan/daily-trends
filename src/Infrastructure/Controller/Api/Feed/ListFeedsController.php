<?php

namespace App\Infrastructure\Controller\Api\Feed;

use App\Application\DTO\Request\Feed\ListFeedsRequest;
use App\Application\DTO\Response\Feed\FeedSummaryResponse;
use App\Application\UseCase\Feed\ListFeeds;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;


#[OA\Tag(name: 'Feeds')]
class ListFeedsController extends AbstractController
{
    public function __construct(
        private readonly ListFeeds $useCase
    ) {
    }
    // La ruta es '' porque en routes.yaml ya definimos el prefijo /feeds
    #[Route('', name: 'api_feeds_list', methods: ['GET'])]
    #[OA\Parameter(name: 'page', in: 'query', description: 'Página a consultar', schema: new OA\Schema(type: 'integer', default: 1))]
    #[OA\Parameter(name: 'limit', in: 'query', description: 'Resultados por página', schema: new OA\Schema(type: 'integer', default: 10))]
    #[OA\Parameter(name: 'sort_by', in: 'query', description: 'Campo de ordenación', schema: new OA\Schema(type: 'string', enum: ['publishedAt', 'title', 'source']))]
    #[OA\Parameter(name: 'sort_order', in: 'query', description: 'Dirección de ordenación', schema: new OA\Schema(type: 'string', enum: ['asc', 'desc']))]
    #[OA\Response(
        response: 200,
        description: 'Listado paginado de noticias',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'items',
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: FeedSummaryResponse::class)) // Ahora sí funcionará
                ),
                new OA\Property(property: 'totalItems', type: 'integer', example: 100),
                new OA\Property(property: 'currentPage', type: 'integer', example: 1),
                new OA\Property(property: 'itemsPerPage', type: 'integer', example: 10),
                new OA\Property(property: 'totalPages', type: 'integer', example: 1)
            ]
        )
    )]

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