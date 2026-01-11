<?php

namespace App\Application\UseCase\Feed;


use App\Domain\Contract\NewsScraperInterface;
use App\Domain\Exception\Scraping\NewsScrapingException;
use App\Domain\Repository\FeedRepositoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

class SyncDailyNews
{
    public function __construct(
        #[AutowireIterator('app.news_scraper')]
        private iterable $scrapers,
        private FeedRepositoryInterface $feedRepository,
        private LoggerInterface $logger
    ) {
    }

    public function execute(int $limit = 5, ?string $requestedSourceName = null): void
    {
        /** @var NewsScraperInterface $scraper */
        foreach ($this->scrapers as $scraper) {
            $sourceName = $scraper->getSource()->name;

            // Si se pidió una fuente específica y no es esta, saltar
            if ($requestedSourceName && $sourceName !== $requestedSourceName) {
                continue;
            }

            try {
                $this->logger->info("Iniciando scraping de: $sourceName");
                //Scrapeamos
                $feeds = $scraper->scrape($limit);
                $newCount = 0;
                $skippedCount = 0;


                foreach ($feeds as $feed) {
                    // Controlamos si ya existe
                    // Todo: manejar feeds borrados (softdelete) de momento se queda asi para no tirar un 500
                    // Si se ha borrado, lo salta. La solucion optima es hacer como en la api, evaluar y restaurar si fuera necesario.
                    $exists = $this->feedRepository->findOneByUrlAndSourceIncludingDeleted(
                        $feed->getUrl(),
                        $feed->getSource()
                    );
                    if ($exists) {
                        $skippedCount++;
                        continue;
                    }
                    $this->feedRepository->save($feed, false);
                    $newCount++;
                }


                if ($newCount > 0) {
                    //Guardamos en bloque para mayor eficiencia
                    $this->feedRepository->flush();
                }

                //TODO crear una respuesta dto con una estructura tipada para dar feedback de que ha pasado aqui.

                $this->logger->info("Finalizado $sourceName. Noticias encontradas: " . count($feeds));
            } catch (NewsScrapingException $e) {
                // Capturamos NUESTRAS excepciones base
                $this->logger->error("Se saltó el scraper {$scraper->getSource()->value} por error: " . $e->getMessage());
            } catch (\Exception $e) {
                $this->logger->error("Error scrapeando $sourceName: " . $e->getMessage());
            }
        }
    }
}
