<?php

namespace App\Infrastructure\Controller\Api\Feed;

use App\Application\UseCase\Feed\DeleteFeed;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Feeds')]
class DeleteFeedController extends AbstractController
{
    public function __construct(
        private readonly DeleteFeed $useCase
    ) {
    }

    #[Route(path: '{id}', name: 'api_feeds_delete', methods: ['DELETE'])]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'UUID de la noticia a eliminar',
        schema: new OA\Schema(type: 'string', format: 'uuid')
    )]
    #[OA\Response(
        response: 204,
        description: 'Noticia eliminada correctamente (Sin contenido)'
    )]
    #[OA\Response(response: 404, description: 'La noticia no existe o ya fue eliminada')]
    public function __invoke(Uuid $id): JsonResponse
    {

        $this->useCase->execute((string) $id);

        //Si todo ha ido bien devolvemos un 204
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}