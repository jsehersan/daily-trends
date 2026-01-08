<?php

namespace App\Infrastructure\Scraping;

use App\Domain\Contract\NewsScraperInterface;
use App\Domain\Entity\Feed;
use App\Domain\Exception\FeedParsingException;
use App\Domain\Exception\FeedUnreachableException;
use App\Infrastructure\Scraping\Client\ScrapingClient;
use Psr\Log\LoggerInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ElPaisScraper implements NewsScraperInterface
{
    public function __construct(
        private ScrapingClient $client,
        private LoggerInterface $logger
    ) {}

    public function getSource(): string
    {
        return 'El Pais';
    }

    public function scrape(int $limit = 5): array
    {
        try {
            $htmlIndex = $this->client->getHtml('https://elpais.com/');
        } catch (FeedUnreachableException $e) {
            // Si la portada está caída, logueamos y devolvemos vacío.
            // No tiene sentido seguir si no tenemos de dónde sacar links.
            $this->logger->error("Error crítico obteniendo portada El País: " . $e->getMessage());
            return [];
        }

        $crawler = new Crawler($htmlIndex);
        $feeds = [];

        $urlsToScrape = $crawler->filter('article h2 > a')->slice(0, $limit)->each(function (Crawler $node) {
            $url = $node->attr('href');
            return str_starts_with($url, 'http') ? $url : 'https://elpais.com' . $url;
        });

        // Obtenemos el detalle de cada noticia
        foreach ($urlsToScrape as $url) {
            try {
                // Devuelve feed o peta
                $feeds[] = $this->scrapeArticleDetail($url);
            } catch (FeedUnreachableException $e) {
                // Error de RED (404, 500, Timeout) en la noticia individual
                $this->logger->warning("Saltando noticia, unreachable: $url - " . $e->getMessage());
            } catch (FeedParsingException $e) {
                // Error de diseño
                $this->logger->warning("Saltando noticia, posible cambio de diseño: $url - " . $e->getMessage());
            } catch (\Exception $e) {
                // Cualquier otra cosa imprevista
                $this->logger->critical("Error desconocido en $url: " . $e->getMessage());
            }
        }

        return $feeds;
    }

    private function scrapeArticleDetail(string $url): ?Feed
    {
        //Si peta, nos lanza un FeedUnreachableException
        $html = $this->client->getHtml($url);

        $crawler = new Crawler($html);
        $feed = new Feed();
        $feed->setUrl($url);
        $feed->setSource($this->getSource());
        $feed->setScrapedAt(new \DateTimeImmutable());


        // Título
        $titleNode = $crawler->filter('h1');

        if ($titleNode->count() === 0) {
            throw FeedParsingException::missingElement('h1', $url);
        }
        $feed->setTitle($titleNode->text());

        // Fecha
        $timeNode = $crawler->filter('time');
        if ($timeNode->count() > 0) {
            $dateStr = $timeNode->attr('datetime');
            try {
                $feed->setPublishedAt(new \DateTimeImmutable($dateStr));
            } catch (\Exception $e) {
                $feed->setPublishedAt(new \DateTimeImmutable());
            }
        } else {
            $feed->setPublishedAt(new \DateTimeImmutable());
        }

        // Imagen principal
        $imageNode = $crawler->filter('article img')->first();
        if ($imageNode->count() > 0) {
            $feed->setImage($imageNode->attr('src'));
        }

        // Cuerpo de la noticia
        $bodyText = [];
        $crawler->filter('article p')->each(function (Crawler $p) use (&$bodyText) {
            $text = $p->text();
            if (strlen($text) > 20) {
                $bodyText[] = $text;
            }
        });

        $feed->setBody(implode("\n\n", $bodyText));

        return $feed;
    }
}
