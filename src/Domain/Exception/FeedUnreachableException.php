<?php

namespace App\Domain\Exception;

class FeedUnreachableException extends NewsScrapingException
{
    public static function fromUrl(string $url, string $reason): self
    {
        return new self(sprintf("No se pudo acceder a %s. Motivo: %s", $url, $reason));
    }
}
