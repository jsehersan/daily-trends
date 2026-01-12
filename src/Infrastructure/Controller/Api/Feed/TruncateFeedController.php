<?php

namespace App\Infrastructure\Controller\Api\Feed;

use App\Domain\Repository\FeedRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class TruncateFeedController extends AbstractController
{
    #[Route('/truncate', name: 'api_feeds_truncate', methods: ['POST'])]
    public function __invoke(FeedRepositoryInterface $repository): JsonResponse
    {
        try {
            $repository->truncate();

            return new JsonResponse([
                'status' => 'success',
                'message' => 'Base de datos limpiada correctamente'
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Error al limpiar la base de datos: ' . $e->getMessage()
            ], 500);
        }
    }
}