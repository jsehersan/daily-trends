<?php

namespace App\Tests\Infrastructure\Scraping;

use App\Domain\Enum\SourceEnum;
use App\Infrastructure\Scraping\Client\ScrapingClient;
use App\Infrastructure\Scraping\ElMundoScraper;
use Psr\Log\NullLogger;

class ElMundoScraperTest extends AbstractScraperTestCase
{
    protected function getFixturesDir(): string
    {
        return __DIR__ . '/../../Fixtures/Scraping/ElMundo';
    }

    protected function getSource(): SourceEnum
    {
        return SourceEnum::EL_MUNDO;
    }

    protected function createScraperInstance(ScrapingClient $client): object
    {
        return new ElMundoScraper($client, new NullLogger());
    }
}