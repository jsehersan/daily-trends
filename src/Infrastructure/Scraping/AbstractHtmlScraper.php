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
    abstract public function getUrl(): string;

    public function scrape(int $limit = 5): array
    {
        try {
            $htmlIndex = $this->client->getHtml($this->getUrl());
        } catch (FeedUnreachableException $e) {
            $this->logger->error("Portada inaccesible ({$this->getSource()->value}): " . $e->getMessage());
            return [];
        }

        $feeds = [];
        //Obtenemos las urls de los artículos
        $urlsToScrape = $this->extractArticleLinks($htmlIndex, $limit);

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

        // Extraer title (obligatorio)
        $title = $this->extractWithFallback($crawler, $selectors['title'], $url, 'text', null);
        if (!$title)
            throw FeedParsingException::missingElement('title', $url);

        // Extraer fecha
        $dateStr = $this->extractWithFallback($crawler, $selectors['date'], $url, 'attr', 'datetime');
        try {
            $publishedAt = $dateStr ? new \DateTimeImmutable($dateStr) : new \DateTimeImmutable();
        } catch (\Exception) {
            $publishedAt = new \DateTimeImmutable();
        }

        // Extraer imagen (opcional)
        $imgSrc = $this->extractWithFallback($crawler, $selectors['image'], $url, 'attr', 'src');

        if (!$imgSrc) {
            $metaImage = $crawler->filter('meta[property="og:image"]');
            if ($metaImage->count() > 0) {
                $imgSrc = $metaImage->attr('content');
                $this->logger->info("Imagen recuperada de meta-tag en: $url");
            }
        }

        // Extraer body (obligatorio)
        $body = $this->extractBodyText($crawler, $selectors['body']);
        if (empty($body)) {
            // Lanzamos la excepcion y lo saltamos, puede ser una landing o algo por el estilo, no una noticia.
            throw FeedParsingException::missingElement('body (no se encuentra el contenido)', $url);
        }

        // Crear Feed con todos los datos recolectados
        return new Feed(
            title: $title,
            url: $url,
            source: $this->getSource(),
            body: $body,
            publishedAt: $publishedAt,
            image: $imgSrc
        );
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


    public function extractArticleLinks(string $html, int $limit): array
    {
        $crawler = new Crawler($html);
        $selectors = $this->getSelectors();

        return $crawler->filter($selectors['article_link'])
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
    }
}
