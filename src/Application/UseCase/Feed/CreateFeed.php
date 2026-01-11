<?php

namespace App\Application\UseCase\Feed;

use App\Application\DTO\Request\Feed\CreateFeedRequest;
use App\Application\DTO\Response\Feed\FeedDetailResponse; // Usamos el Response Completo
use App\Domain\Entity\Feed;
use App\Domain\Enum\SourceEnum;
use App\Domain\Exception\Feed\FeedAlreadyExistsException;
use App\Domain\Repository\FeedRepositoryInterface;

readonly class CreateFeed
{
    public function __construct(
        private FeedRepositoryInterface $repository
    ) {
    }

    public function execute(CreateFeedRequest $request): FeedDetailResponse
    {
        //Manejamos el intento de crear un nuevo feed con una url/source anteriormente borrado
        $existingFeed = $this->repository->findOneByUrlAndSourceIncludingDeleted(
            $request->url,
            SourceEnum::MANUAL,
        );
        if ($existingFeed !== null) {
            $feed = $this->handleExistingFeed($existingFeed, $request);
        } else {
            $feed = $this->createNewFeed($request);
        }

        $this->repository->save($feed, true);


        return FeedDetailResponse::fromEntity($feed);
    }

    private function handleExistingFeed(Feed $feed, CreateFeedRequest $request): Feed
    {
        // Si no está borrado, es un duplicado real
        if (!$feed->isDeleted()) {
            throw FeedAlreadyExistsException::withUrl($request->url, SourceEnum::MANUAL->value);
        }

        // Si no, restauramos
        $feed->recover();

        // Actualizamos con lo que venga del request
        $feed->update(
            title: $request->title,
            body: $request->body,
            publishedAt: new \DateTimeImmutable($request->publishedAt),
            image: $request->image
        );

        return $feed;
    }
    private function createNewFeed(CreateFeedRequest $request): Feed
    {
        return new Feed(
            title: $request->title,
            url: $request->url,
            source: SourceEnum::MANUAL,
            body: $request->body,
            publishedAt: new \DateTimeImmutable($request->publishedAt),
            image: $request->image
        );
    }
}