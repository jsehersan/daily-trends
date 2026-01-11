<?php

namespace App\Application\UseCase\Feed;

use App\Application\DTO\Request\Feed\CreateFeedRequest;
use App\Application\DTO\Response\Feed\FeedDetailResponse; // Usamos el Response Completo
use App\Domain\Entity\Feed;
use App\Domain\Enum\SourceEnum;
use App\Domain\Repository\FeedRepositoryInterface;

readonly class CreateFeed
{
    public function __construct(
        private FeedRepositoryInterface $repository
    ) {
    }

    public function execute(CreateFeedRequest $request): FeedDetailResponse
    {

        $publishedAt = new \DateTimeImmutable($request->publishedAt);

        $feed = new Feed(
            title: $request->title,
            url: $request->url,
            source: SourceEnum::MANUAL,
            body: $request->body,
            publishedAt: $publishedAt,
            image: $request->image
        );


        $this->repository->save($feed, true);


        return FeedDetailResponse::fromEntity($feed);
    }
}