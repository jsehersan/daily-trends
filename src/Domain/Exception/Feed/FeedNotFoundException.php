<?php

namespace App\Domain\Exception\Feed;

use App\Domain\Exception\DomainException;

class FeedNotFoundException extends DomainException
{
    public static function fromId(string $id): self
    {
        return new self(sprintf('Feed with ID "%s" not found.', $id), 404);
    }
}