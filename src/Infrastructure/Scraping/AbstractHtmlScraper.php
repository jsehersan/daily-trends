<?php

namespace App\Infrastructure\Scraping;

use App\Domain\Contract\NewsScraperInterface;
use App\Domain\Entity\Feed;
use App\Domain\Enum\SourceEnum;
use App\Domain\Exception\FeedParsingException;
use App\Domain\Exception\FeedUnreachableException;
use App\Infrastructure\Scraping\Client\ScrapingClient;
use Psr\Log\LoggerInterface;
use Symfony\Component\DomCrawler\Crawler;

abstract class AbstractHtmlScraper implements NewsScraperInterface
{
    public function __construct(
        protected ScrapingClient $client,
        protected LoggerInterface $logger
    ) {
    }

    abstract protected function getSelectors(): array;
    abstract protected function getUrl(): string;

    public function scrape(int $limit = 5): array
    {
        try {
            $htmlIndex = $this->client->getHtml($this->getUrl());
        } catch (FeedUnreachableException $e) {
            $this->logger->error("Portada inaccesible ({$this->getSource()->value}): " . $e->getMessage());
            return [];
        }

        $crawler = new Crawler($htmlIndex);
        $feeds = [];
        $selectors = $this->getSelectors();
        //Obtenemos las urls de los artículos
        $urlsToScrape = $crawler->filter($selectors['article_link'])
            ->slice(0, $limit)
            ->each(function (Crawler $node) {
                $url = $node->attr('href');
                // Normalización de URL relativa a absoluta
                if (!str_starts_with($url, 'http')) {
                    $baseUrl = rtrim($this->getUrl(), '/');
                    $path = ltrim($url, '/');
                    return "$baseUrl/$path";
                }
                return $url;
            });

        foreach ($urlsToScrape as $url) {
            try {
                //Obtenemos el detalle del artículo
                $feeds[] = $this->scrapeArticleDetail($url);
            } catch (FeedUnreachableException $e) {
                $this->logger->warning("No se puede acceder a {$this->getSource()->value}: $url");
            } catch (FeedParsingException $e) {
                $this->logger->warning("Posible cambio en el diseño de la página:{$this->getSource()->value}: $url - " . $e->getMessage());
            } catch (\Exception $e) {
                $this->logger->critical("Error desconocido: " . $e->getMessage());
            }
        }

        return $feeds;
    }

    private function scrapeArticleDetail(string $url): Feed
    {
        $html = $this->client->getHtml($url);
        $crawler = new Crawler($html);
        $selectors = $this->getSelectors();

        $feed = new Feed();
        $feed->setUrl($url);
        $feed->setSource($this->getSource());
        $feed->setScrapedAt(new \DateTimeImmutable());

        // Validaciones y extracción usando Fallbacks
        $title = $this->extractWithFallback($crawler, $selectors['title'], $url, 'text', null);
        if (!$title)
            throw FeedParsingException::missingElement('title', $url);
        $feed->setTitle($title);

        $dateStr = $this->extractWithFallback($crawler, $selectors['date'], $url, 'attr', 'datetime');
        try {
            $feed->setPublishedAt($dateStr ? new \DateTimeImmutable($dateStr) : new \DateTimeImmutable());
        } catch (\Exception) {
            $feed->setPublishedAt(new \DateTimeImmutable());
        }

        $imgSrc = $this->extractWithFallback($crawler, $selectors['image'], $url, 'attr', 'src');
        if ($imgSrc)
            $feed->setImage($imgSrc);

        $body = $this->extractBodyText($crawler, $selectors['body']);
        $feed->setBody($body);

        return $feed;
    }

    //Reintentamos tantas como selectores tengamos configurados, avisamos si se ha usado un fallback para generar una alerta el equipo y revisar si ha cambiado el diseño.
    protected function extractWithFallback(Crawler $crawler, array $candidates, string $url, string $type = 'text', ?string $attrName = null): ?string
    {
        foreach ($candidates as $index => $selector) {
            $node = $crawler->filter($selector);
            if ($node->count() > 0) {
                $result = ($type === 'attr' && $attrName) ? $node->attr($attrName) : $node->text();
                if (!empty(trim($result))) {
                    if ($index > 0)
                        $this->logger->info("Fallback usado en $url: '$selector'");
                    return trim($result);
                }
            }
        }
        return null;
    }
    //Montamos el body con los párrafos que encontremos
    protected function extractBodyText(Crawler $crawler, array $candidates): ?string
    {
        foreach ($candidates as $selector) {
            $nodes = $crawler->filter($selector);
            if ($nodes->count() > 0) {
                $textParts = [];
                $nodes->each(function (Crawler $p) use (&$textParts) {
                    $t = trim($p->text());
                    if (strlen($t) > 30)
                        $textParts[] = $t;
                });
                if (!empty($textParts))
                    return implode("\n\n", $textParts);
            }
        }
        return null;
    }
}