<?php

namespace App\Infrastructure\Persistence;

use App\Application\DTO\Response\PaginatedResult;
use App\Domain\Entity\Feed;
use App\Domain\Repository\FeedRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
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

    public function truncate(): void
    {
        $connection = $this->getEntityManager()->getConnection();
        $tableName = $this->getEntityManager()->getClassMetadata(Feed::class)->getTableName();
        $connection->executeStatement('TRUNCATE TABLE ' . $tableName);
    }
    public function findAllPaginated(int $page, int $limit, string $sortBy, string $sortOrder): PaginatedResult
    {
        // Debe venir bien del request pero lo mapeo para más seguridad
        $sortMapping = [
            'publishedAt' => 'publishedAt',
            'title' => 'title',
            'source' => 'source'
        ];
        $sortField = $sortMapping[$sortBy] ?? 'publishedAt';

        // Hacemos el query
        $query = $this->createQueryBuilder('f')
            ->orderBy("f.$sortField", $sortOrder)
            ->getQuery();

        // Paginado
        $query->setFirstResult(($page - 1) * $limit);
        $query->setMaxResults($limit);

        $paginator = new Paginator($query);
        $totalItems = count($paginator);
        $totalPages = (int) ceil($totalItems / $limit);

        return new PaginatedResult(
            //Hasta que no se recorra el paginator no se ejecuta la query, hay que forzarlo.
            items: iterator_to_array($paginator),
            totalItems: $totalItems,
            currentPage: $page,
            itemsPerPage: $limit,
            totalPages: $totalPages
        );
    }

    public function findById(string $id): ?Feed
    {
        return $this->find($id);
    }
}
