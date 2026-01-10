<?php

namespace App\Application\UseCase\Feed;

use App\Application\DTO\Response\Feed\FeedSummaryResponse;
use App\Application\DTO\Response\PaginatedResult;
use App\Domain\Repository\FeedRepositoryInterface;

readonly class ListFeeds
{
    public function __construct(
        private FeedRepositoryInterface $feedRepository
    ) {
    }

    public function execute(int $page, int $limit, string $sortBy, string $sortOrder): PaginatedResult
    {
        // Obtenemos los datos en crudo del repositorio
        $rawResult = $this->feedRepository->findAllPaginated($page, $limit, $sortBy, $sortOrder);

        // Convertimos las entidades a DTOs de respuesta
        $dtos = array_map(
            fn($feed) => FeedSummaryResponse::fromEntity($feed),
            $rawResult->items
        );

        // Devolvemos el resultado final limpio filtrado por el response
        return new PaginatedResult(
            items: $dtos,
            totalItems: $rawResult->totalItems,
            currentPage: $rawResult->currentPage,
            itemsPerPage: $rawResult->itemsPerPage,
            totalPages: $rawResult->totalPages
        );
    }
}