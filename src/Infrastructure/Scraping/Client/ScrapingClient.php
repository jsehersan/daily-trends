<?php

namespace App\Infrastructure\Scraping\Client;

use App\Domain\Exception\Scraping\FeedUnreachableException;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ScrapingClient
{
    private const MAX_RETRIES = 3;
    private const DELAY_MS = 2000;

    public function __construct(
        private HttpClientInterface $client,
        private LoggerInterface $logger
    ) {
    }

    /**
     * @throws FeedUnreachableException Si tras reintentos no se puede obtener el HTML
     */
    public function getHtml(string $url): string
    {
        $attempt = 0;

        while ($attempt < self::MAX_RETRIES) {
            $attempt++;
            try {
                $response = $this->client->request('GET', $url);
                $statusCode = $response->getStatusCode();

                if ($statusCode === 200) {
                    return $response->getContent();
                }

                if ($statusCode === 404) {
                    //404 , no se reintenta.
                    throw FeedUnreachableException::fromUrl($url, "Error 404 Not Found");
                }

                // Si es 500, lanzamos excepción genérica para forzar el catch de abajo y reintentar
                throw new \Exception("Server Error $statusCode");
            } catch (\Exception $e) {
                // Si es el último intento, lanzamos la excepción 
                if ($attempt === self::MAX_RETRIES) {
                    $this->logger->error("Fallo definitivo en $url");
                    throw FeedUnreachableException::fromUrl($url, $e->getMessage());
                }

                // Si no, esperamos y seguimos
                $this->logger->warning("Intento $attempt fallido para $url: " . $e->getMessage());
                usleep(self::DELAY_MS * $attempt * 1000);
            }
        }

        throw FeedUnreachableException::fromUrl($url, "Max retries reached");
    }
}
