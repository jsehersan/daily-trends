<?php

namespace App\Infrastructure\Controller\Api\Feed;

use App\Application\UseCase\Feed\SyncDailyNews;
use App\Domain\Repository\FeedRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class SyncFeedController extends AbstractController
{
    #[Route('/sync', name: 'api_feeds_sync', methods: ['POST'])]
    public function __invoke(
        SyncDailyNews $syncDailyNews, 
        FeedRepositoryInterface $repository
    ): JsonResponse {
        try {
            // Ejecutamos sin depender del return del caso de uso
            $syncDailyNews->execute();

            // Consultamos al repositorio para dar un dato real de la situación actual
            // Usamos tu método de paginado para obtener el total rápidamente
            $paginated = $repository->findAllPaginated(1, 1, 'publishedAt', 'desc');
            $totalInDb = $paginated->totalItems;

            return new JsonResponse([
                'status' => 'success',
                'message' => 'Proceso de sincronización finalizado correctamente',
                'data' => [
                    'total_news_available' => $totalInDb,
                    'status_code' => 'SYNC_OK',
                    'timestamp' => (new \DateTimeImmutable())->format('H:i:s')
                ]
            ], 200);

        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Error durante la sincronización: ' . $e->getMessage()
            ], 500);
        }
    }
}