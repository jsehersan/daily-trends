<?php

namespace App\Application\DTO\Response;

readonly class PaginatedResult
{
    public function __construct(
        public array $items,
        public int $totalItems,
        public int $currentPage,
        public int $itemsPerPage,
        public int $totalPages
    ) {
    }
}