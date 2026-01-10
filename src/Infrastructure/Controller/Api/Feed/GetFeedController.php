<?php

namespace App\Infrastructure\Controller\Api\Feed;

use App\Application\UseCase\Feed\GetFeed;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

class GetFeedController extends AbstractController
{
    public function __construct(
        private readonly GetFeed $useCase
    ) {
    }

    //Si manda algo que no se sea un uuid peta, nos ahorramos que continue con el request.
    #[Route(
        path: '/{id}',
        name: 'api_feeds_get',
        methods: ['GET']
    )]
    public function __invoke(
        string $id
    ): JsonResponse {

        if (!Uuid::isValid($id)) {
            // Lanzamos una excepción HTTP 400, si no validamos uuid peta doctrine,
            # si lo ponemos como requirement se salta la ruta y tira un 404
            throw new BadRequestHttpException(sprintf('El ID "%s" no es un UUID válido.', $id));
        }
        $result = $this->useCase->execute($id);

        return $this->json($result);
    }
}