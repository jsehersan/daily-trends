<?php
/*

namespace App\Infrastructure\Scraping;

use App\Domain\Contract\NewsScraperInterface;
use App\Domain\Entity\Feed;

  Esta podria ser otra clase abstracta con un comportamiento distinto para los sitios que usan SPA o cargan contenido dinamico tipo vue/React
  Usariamos otra forma de traernos los datos esperando a que carge toda la pagina 
  y luego creariamos clases para extiendan de esta con la misma interfaz NewsScraperInterface tantas como sitios SPA tengamos para scrapear
 

abstract class AbstractSpaScraper implements NewsScraperInterface
{
    public function __construct(
        //protected ScrapingSPAClient $client,
        //protected LoggerInterface $logger
    ) {
    }

    abstract protected function getSelectors(): array;
    abstract protected function getUrl(): string;

    public function scrape(int $limit = 5): array
    {
        return [new Feed()];
    }


}
    */