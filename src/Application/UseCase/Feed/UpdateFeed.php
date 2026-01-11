<?php

namespace App\Application\UseCase\Feed;

use App\Application\DTO\Request\Feed\UpdateFeedRequest;
use App\Application\DTO\Response\Feed\FeedDetailResponse;
use App\Domain\Enum\SourceEnum;
use App\Domain\Exception\Feed\FeedNotFoundException;
use App\Domain\Repository\FeedRepositoryInterface;

readonly class UpdateFeed
{
    public function __construct(
        private FeedRepositoryInterface $repository
    ) {
    }

    public function execute(string $id, UpdateFeedRequest $request): FeedDetailResponse
    {
        $feed = $this->repository->findById($id);

        if (null === $feed) {
            throw FeedNotFoundException::fromId($id);
        }

        // Conversión de tipos 
        $source = $request->source ? SourceEnum::from($request->source) : null;
        $publishedAt = $request->publishedAt ? new \DateTimeImmutable($request->publishedAt) : null;


        $feed->update(
            title: $request->title,
            url: $request->url,
            body: $request->body,
            source: $source,
            publishedAt: $publishedAt,
            image: $request->image,
            removeImage: $request->removeImage
        );

        //Guardamos
        $this->repository->save($feed, true);

        return FeedDetailResponse::fromEntity($feed);
    }
}