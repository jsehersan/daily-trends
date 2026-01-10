<?php

namespace App\Application\DTO\Response\Feed;

use App\Domain\Entity\Feed;

readonly class FeedSummaryResponse
{
    public function __construct(
        public string $id,
        public string $title,
        public string $source,
        public string $url,
        public string $publishedAt,
        public ?string $image
    ) {
    }

    public static function fromEntity(Feed $feed): self
    {
        return new self(
            id: $feed->getId()->toRfc4122(),
            title: $feed->getTitle(),
            source: $feed->getSource()->value,
            url: $feed->getUrl(),
            publishedAt: $feed->getPublishedAt()->format(\DateTime::ATOM),
            image: $feed->getImage()
        );
    }
}