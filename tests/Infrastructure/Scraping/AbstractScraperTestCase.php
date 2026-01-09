<?php

namespace App\Tests\Infrastructure\Scraping;

use App\Domain\Entity\Feed;
use App\Domain\Enum\SourceEnum;
use App\Infrastructure\Scraping\Client\ScrapingClient;
use PHPUnit\Framework\TestCase;

abstract class AbstractScraperTestCase extends TestCase
{
    abstract protected function getFixturesDir(): string;
    abstract protected function getSource(): SourceEnum;
    abstract protected function createScraperInstance(ScrapingClient $client): object;

    protected function setUp(): void
    {
        if (!is_dir($this->getFixturesDir())) {
            $this->markTestSkipped('Fixtures no encontradas');
        }
    }

    public function testScrapeUsesRealFixturesCorrectly(): void
    {
        // Minimo tenemos portada para obtener links
        $portadaPath = $this->getFixturesDir() . '/portada.html';
        if (!file_exists($portadaPath)) {
            $this->fail('No se encuentra el archivo portada.html en el directorio de fixtures.');
        }
        $htmlPortada = file_get_contents($portadaPath);

        // Creamos instancia temporar con stub para simular el scrapingClient, si lo hacemos como Mock nos tira un warning que no he investigado
        $tempClientStub = $this->createStub(ScrapingClient::class);

        // Usamos el factory method abstracto en lugar de 'new ElMundoScraper' directo
        $tempScraper = $this->createScraperInstance($tempClientStub);

        // Obtenemos urls reales de la portada
        $realLinks = $tempScraper->extractArticleLinks($htmlPortada, 5);

        // Generamos un map con url=>html para pasarselo a scraper simulando una peticion http
        $urlMap = [$tempScraper->getUrl() => $htmlPortada];

        foreach ($realLinks as $index => $url) {
            $filePath = $this->getFixturesDir() . "/article_{$index}.html";
            if (file_exists($filePath)) {
                $urlMap[$url] = file_get_contents($filePath);
            }
        }

        // Configuramos el scrapingClient para usarlo después pasándole el map
        $clientStub = $this->createStub(ScrapingClient::class);
        $clientStub->method('getHtml')
            ->willReturnCallback(function (string $url) use ($urlMap) {
                if (isset($urlMap[$url])) {
                    return $urlMap[$url];
                }
                // Excepción controlada si se solicita una URL no prevista en las fixtures
                throw new \RuntimeException("Intento de acceso a URL no mapeada en el test: $url");
            });

        // Ejecución del scraper con el cliente simulado configurado
        $scraper = $this->createScraperInstance($clientStub);
        $feeds = $scraper->scrape(5);

        $this->assertNotEmpty($feeds, 'El scraper no ha devuelto resultados.');

        // Quitamos el de la portada
        $expectedCount = count($urlMap) - 1;
        $this->assertCount($expectedCount, $feeds, "El número de noticias extraídas no coincide con las fixtures disponibles.");

        foreach ($feeds as $feed) {
            $this->assertInstanceOf(Feed::class, $feed);

            // Aquí usamos el Enum
            $this->assertEquals($this->getSource(), $feed->getSource());

            // Verificación de los datos extraídos
            $this->assertNotEmpty($feed->getTitle(), "Se ha detectado un título vacío en la noticia {$feed->getUrl()}.");
            $this->assertNotEmpty($feed->getBody(), "Se ha detectado un cuerpo vacío en la noticia {$feed->getUrl()}.");
            $this->assertNotNull($feed->getPublishedAt(), "Se ha detectado una fecha nula en la noticia {$feed->getUrl()}.");
            // Intentar descubir una noticia que sea en realidad un link a otro lugar tipo contacto o algo asi
            $this->assertGreaterThan(10, strlen($feed->getTitle()), "El título es corto: '{$feed->getTitle()}'");
            $this->assertGreaterThan(100, strlen($feed->getBody()), "El cuerpo es muy corto ({$feed->getBody()})");
        }
    }
}