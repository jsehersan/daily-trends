<?php

namespace App\Domain\Exception\Feed;

use App\Domain\Exception\DomainException;

class FeedAlreadyExistsException extends DomainException
{
    public static function withUrl(string $url, string $source): self
    {
        // Código 409 (Conflict) para que el Listener sepa qué devolver
        return new self(
            sprintf('A feed from source "%s" with URL "%s" already exists.', $source, $url),
            409
        );
    }
}