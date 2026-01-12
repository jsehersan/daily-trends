<?php

namespace App\Infrastructure\Persistence\Mongo;

use App\Application\DTO\Response\PaginatedResult;
use App\Domain\Entity\Feed;
use App\Domain\Enum\SourceEnum;
use App\Domain\Exception\Feed\FeedAlreadyExistsException;
use App\Domain\Repository\FeedRepositoryInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Iterator\Iterator;

class MongoFeedRepository implements FeedRepositoryInterface
{
    public function __construct(
        private DocumentManager $dm
    ) {}

    public function save(Feed $feed, bool $flush = false): void
    {
        // Prevenimos la excepción (Select previo)
        $existing = $this->findOneByUrlAndSource($feed->getUrl(), $feed->getSource());
        
        if ($existing && $existing->getId() !== $feed->getId()) {
            throw FeedAlreadyExistsException::withUrl(
                $feed->getUrl(),
                $feed->getSource()->value
            );
        }

        $this->dm->persist($feed);
        if ($flush) {
            $this->dm->flush();
        }
    }

    public function findOneByUrlAndSource(string $url, SourceEnum $source): ?Feed
    {
        return $this->dm->getRepository(Feed::class)->findOneBy([
            'url' => $url,
            'source' => $source->value // En Mongo usamos el value del Enum
        ]);
    }

    public function flush(): void
    {
        $this->dm->flush();
    }

    public function truncate(): void
    {
        // En MongoDB "truncate" equivale a drop o eliminar todos los documentos
        $this->dm->getDocumentCollection(Feed::class)->deleteMany([]);
    }

    public function findAllPaginated(int $page, int $limit, string $sortBy, string $sortOrder): PaginatedResult
    {
        $sortMapping = [
            'publishedAt' => 'publishedAt',
            'title' => 'title',
            'source' => 'source'
        ];
        $sortField = $sortMapping[$sortBy] ?? 'publishedAt';
        $direction = strtolower($sortOrder) === 'asc' ? 1 : -1;

        $repository = $this->dm->getRepository(Feed::class);
        
        // Query Builder de MongoDB
        $qb = $repository->createQueryBuilder()
            ->sort($sortField, $direction);

        // Clonamos para contar sin los límites de paginación
        $countQuery = clone $qb;
        $totalItems = $countQuery->count()->getQuery()->execute();

        // Aplicamos límites
        $items = $qb->limit($limit)
            ->skip(($page - 1) * $limit)
            ->getQuery()
            ->execute();

        $totalPages = (int) ceil($totalItems / $limit);

        return new PaginatedResult(
            items: iterator_to_array($items),
            totalItems: $totalItems,
            currentPage: $page,
            itemsPerPage: $limit,
            totalPages: $totalPages
        );
    }

    public function findById(string $id): ?Feed
    {
        return $this->dm->getRepository(Feed::class)->find($id);
    }

    public function findOneByUrlAndSourceIncludingDeleted(string $url, SourceEnum $source): ?Feed
    {
        // MongoDB ODM también tiene Filters. Si usas uno para SoftDelete:
        $filters = $this->dm->getFilterCollection();
        $isFilterEnabled = $filters->isEnabled('soft_delete');

        if ($isFilterEnabled) {
            $filters->disable('soft_delete');
        }

        $feed = $this->findOneByUrlAndSource($url, $source);

        if ($isFilterEnabled) {
            $filters->enable('soft_delete');
        }

        return $feed;
    }
}