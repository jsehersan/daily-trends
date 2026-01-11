<?php

namespace App\Application\UseCase\Feed;

use App\Domain\Exception\Feed\FeedNotFoundException;
use App\Domain\Repository\FeedRepositoryInterface;

readonly class DeleteFeed
{
    public function __construct(
        private FeedRepositoryInterface $repository
    ) {
    }

    public function execute(string $id): void
    {
        //Si ya la hemos borrado (softdelete) no tirará una FeedNotFoundException gracias al filtro
        $feed = $this->repository->findById($id);

        if (null === $feed) {
            throw FeedNotFoundException::fromId($id);
        }

        //Rellenamos el deletedAt y preparamos el entity para persistir
        $feed->softDelete();

        //Actualizamos, no borramos. A partir de ahora el filtro de doctrine lo oculta.
        $this->repository->save($feed, true);
    }
}