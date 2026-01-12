<?php

namespace App\Infrastructure\Persistence\Doctrine;

use App\Application\DTO\Response\PaginatedResult;
use App\Domain\Entity\Feed;
use App\Domain\Enum\SourceEnum;
use App\Domain\Exception\Feed\FeedAlreadyExistsException;
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

        //Prevenimos la exception a cambio de un select extra TODO: revisar el impacto de en el rendimiento
        $existing = $this->findOneBy(['url' => $feed->getUrl(), 'source' => $feed->getSource()]);
        if ($existing && $existing->getId() !== $feed->getId()) {
            throw FeedAlreadyExistsException::withUrl(
                $feed->getUrl(),
                $feed->getSource()->value
            );
        }

        $this->getEntityManager()->persist($feed);
        if ($flush) {
            $this->getEntityManager()->flush();
        }

    }

    public function findOneByUrlAndSource(string $url, SourceEnum $source): ?Feed
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


    public function findOneByUrlAndSourceIncludingDeleted(string $url, SourceEnum $source): ?Feed
    {
        // Desactivamos filtros
        if ($this->getEntityManager()->getFilters()->isEnabled('soft_delete')) {
            $this->getEntityManager()->getFilters()->disable('soft_delete');
        }

        // Buscamos
        $feed = $this->findOneBy([
            'url' => $url,
            'source' => $source
        ]);

        // Volvemos a activar
        if (!$this->getEntityManager()->getFilters()->isEnabled('soft_delete')) {
            $this->getEntityManager()->getFilters()->enable('soft_delete');
        }

        return $feed;
    }
}
