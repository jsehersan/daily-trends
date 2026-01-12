<?php

namespace App\Infrastructure\Controller\Api\Feed;

use App\Application\DTO\Request\Feed\UpdateFeedRequest;
use App\Application\DTO\Response\Feed\FeedDetailResponse;
use App\Application\UseCase\Feed\UpdateFeed;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;

#[OA\Tag(name: 'Feeds')]
class UpdateFeedController extends AbstractController
{
    public function __construct(
        private readonly UpdateFeed $useCase
    ) {
    }

    #[Route(path: '{id}', name: 'api_feeds_update', methods: ['PUT'])]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'UUID de la noticia a actualizar',
        schema: new OA\Schema(type: 'string', format: 'uuid')
    )]
    #[OA\RequestBody(
        description: 'Datos nuevos para la noticia',
        required: true,
        content: new OA\JsonContent(
            ref: new Model(type: UpdateFeedRequest::class)
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Noticia actualizada correctamente',
        content: new OA\JsonContent(
            ref: new Model(type: FeedDetailResponse::class)
        )
    )]
    #[OA\Response(response: 400, description: 'ID inválido o petición mal formada')]
    #[OA\Response(response: 404, description: 'La noticia no existe')]
    #[OA\Response(response: 422, description: 'Error de validación (datos incorrectos)')]
    public function __invoke(
        Uuid $id,
        #[MapRequestPayload] UpdateFeedRequest $request
    ): JsonResponse {

        $response = $this->useCase->execute((string) $id, $request);

        return $this->json($response);
    }
}