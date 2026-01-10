<?php

namespace App\Domain\Exception\Scraping;

class FeedParsingException extends NewsScrapingException
{
    public static function missingElement(string $selector, string $url): self
    {
        return new self(sprintf("No se encontró el elemento '%s' en %s. Posible cambio de diseño.", $selector, $url));
    }
}
