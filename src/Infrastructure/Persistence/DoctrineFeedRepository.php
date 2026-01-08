<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Entity\Feed;
use App\Domain\Repository\FeedRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DoctrineFeedRepository extends ServiceEntityRepository implements FeedRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Feed::class);
    }

    public function save(Feed $feed, bool $flush = false): void
    {
        $this->getEntityManager()->persist($feed);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findOneByUrlAndSource(string $url, string $source): ?Feed
    {
        return $this->findOneBy(['url' => $url, 'source' => $source]);
    }

    public function flush(): void
    {
        $this->getEntityManager()->flush();
    }
}
