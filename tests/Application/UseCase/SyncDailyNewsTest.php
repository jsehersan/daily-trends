<?php

namespace App\Tests\Application\UseCase;

use App\Application\UseCase\Feed\SyncDailyNews;
use App\Domain\Contract\NewsScraperInterface;
use App\Domain\Entity\Feed;
use App\Domain\Enum\SourceEnum;
use App\Domain\Repository\FeedRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Faker\Factory;
use Psr\Log\LoggerInterface;

class SyncDailyNewsTest extends TestCase
{
    private $faker;

    public function setUp(): void
    {
        $this->faker = Factory::create('es_ES');

    }

    public function testExecuteSavesFeedsToRepository(): void
    {
        // Creamos feeds
        $numberOfFeeds = 5;
        $feeds = [];
        for ($i = 0; $i < $numberOfFeeds; $i++) {
            $feeds[] = $this->createDummyFeed();
        }


        $mockRepository = $this->createMock(FeedRepositoryInterface::class);

        // Mock repository debe ser llamado el save x veces y como parametro un feed 
        $mockRepository->expects($this->exactly($numberOfFeeds))
            ->method('save')
            ->with($this->isInstanceOf(Feed::class));


        // Stub simple que retorna un SourceEunum y hace un fake scraping devolviendo los feeds
        $scraperStub = $this->createStub(NewsScraperInterface::class);
        $scraperStub->method('getSource')->willReturn(SourceEnum::EL_MUNDO);
        $scraperStub->method('scrape')->willReturn($feeds);

        // Logger para que no tire warnings
        $loggerStub = $this->createStub(LoggerInterface::class);

        // Instanciamos el use case y ejecutamos
        $useCase = new SyncDailyNews(
            [$scraperStub],   // Scrapers (Array)
            $mockRepository,  // Repositorio
            $loggerStub       // Logger
        );

        $useCase->execute();

        // TODO : $result = $useCase->execute(); Estaria bien que retorne un DTO  y pasarle asserts al resultado, si da tiempo más adelante.
    }

    private function createDummyFeed(): Feed
    {
        return new Feed(
            title: $this->faker->sentence(8),
            url: $this->faker->url(),
            source: SourceEnum::EL_MUNDO,
            body: $this->faker->text(),
            publishedAt: new \DateTimeImmutable(),
            image: $this->faker->imageUrl()
        );
    }
}