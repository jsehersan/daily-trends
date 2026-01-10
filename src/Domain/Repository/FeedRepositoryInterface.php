<?php

namespace App\Domain\Repository;

use App\Application\DTO\Response\PaginatedResult;
use App\Domain\Entity\Feed;

interface FeedRepositoryInterface
{
    public function save(Feed $feed, bool $flush = false): void;
    public function findOneByUrlAndSource(string $url, string $source): ?Feed;
    public function flush(): void;
    public function truncate(): void;
    public function findAllPaginated(int $page, int $limit, string $sortBy, string $sortOrder): PaginatedResult;
}
