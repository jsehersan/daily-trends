<?php

namespace App\Application\UseCase\Feed;

use App\Application\DTO\Response\Feed\FeedDetailResponse;
use App\Domain\Exception\Feed\FeedNotFoundException;
use App\Domain\Repository\FeedRepositoryInterface;

readonly class GetFeed
{
    public function __construct(
        private FeedRepositoryInterface $feedRepository
    ) {
    }

    public function execute(string $id): FeedDetailResponse
    {
        $feed = $this->feedRepository->findById($id);
        if (!$feed) {
            throw FeedNotFoundException::fromId($id);
        }
        return FeedDetailResponse::fromEntity($feed);
    }
}