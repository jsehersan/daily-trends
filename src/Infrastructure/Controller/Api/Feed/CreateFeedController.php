<?php

namespace App\Infrastructure\Controller\Api\Feed;

use App\Application\DTO\Request\Feed\CreateFeedRequest;
use App\Application\DTO\Response\Feed\FeedDetailResponse;
use App\Application\UseCase\Feed\CreateFeed;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;
#[OA\Tag(name: 'Feeds')]
class CreateFeedController extends AbstractController
{
    public function __construct(
        private readonly CreateFeed $useCase
    ) {
    }

    #[Route(path: '', name: 'api_feeds_create', methods: ['POST'])]
    #[OA\RequestBody(
        description: 'Datos para crear la noticia',
        required: true,
        content: new OA\JsonContent(
            ref: new Model(type: CreateFeedRequest::class)
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Noticia creada exitosamente',
        content: new OA\JsonContent(
            ref: new Model(type: FeedDetailResponse::class)
        )
    )]
    #[OA\Response(response: 400, description: 'Solicitud incorrecta')]
    #[OA\Response(response: 422, description: 'Error de validación (campos faltantes o inválidos)')]
    #[OA\Response(response: 409, description: 'Conflicto: La noticia ya existe')]
    public function __invoke(
        #[MapRequestPayload] CreateFeedRequest $request
    ): JsonResponse {

        $response = $this->useCase->execute($request);

        return $this->json($response, Response::HTTP_CREATED);
    }
}