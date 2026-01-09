<?php

namespace App\Domain\Repository;

use App\Domain\Entity\Feed;

interface FeedRepositoryInterface
{
    public function save(Feed $feed, bool $flush = false): void;
    public function findOneByUrlAndSource(string $url, string $source): ?Feed;
    public function flush(): void;
    public function truncate(): void;
}
