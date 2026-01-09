<?php

namespace App\Tests\Infrastructure\Scraping;

use App\Domain\Enum\SourceEnum;
use App\Infrastructure\Scraping\Client\ScrapingClient;
use App\Infrastructure\Scraping\ElPaisScraper;
use Psr\Log\NullLogger;

class ElPaisScraperTest extends AbstractScraperTestCase
{
    protected function getFixturesDir(): string
    {
        return __DIR__ . '/../../Fixtures/Scraping/ElPais';
    }

    protected function getSource(): SourceEnum
    {
        return SourceEnum::EL_PAIS;
    }

    protected function createScraperInstance(ScrapingClient $client): object
    {
        return new ElPaisScraper($client, new NullLogger());
    }
}