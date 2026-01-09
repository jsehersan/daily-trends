<?php

namespace App\Command;

use App\Domain\Contract\NewsScraperInterface;
use App\Infrastructure\Scraping\AbstractHtmlScraper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(
    name: 'app:fixtures:download',
    description: 'Descarga HTML real (Portada + 5 Artículos) de todos los scrapers registrados.',
)]
class DownloadFixturesCommand extends Command
{
    private string $baseFixturesDir;

    public function __construct(
        private HttpClientInterface $client,
        KernelInterface $kernel,
        // Inyecta todos los scrapers
        #[AutowireIterator('app.news_scraper')]
        private iterable $scrapers
    ) {
        $this->baseFixturesDir = $kernel->getProjectDir() . '/tests/Fixtures/Scraping';
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Generando Snapshot Real (Portada + Detalle)');

        if (!is_dir($this->baseFixturesDir)) {
            mkdir($this->baseFixturesDir, 0777, true);
        }

        foreach ($this->scrapers as $scraper) {
            $reflection = new \ReflectionClass($scraper);
            $cleanName = str_replace('Scraper', '', $reflection->getShortName());

            $targetDir = $this->baseFixturesDir . '/' . $cleanName;
            $baseUrl = $scraper->getUrl();

            $io->section("Procesando: $cleanName");

            // Crear directorio específico del medio
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }

            // Descargar portada
            $io->text("Descargando Portada: $baseUrl");
            try {
                $htmlPortada = $this->fetchContent($baseUrl);
                file_put_contents($targetDir . '/portada.html', $htmlPortada);
                $io->text("Portada guardada.");
            } catch (\Exception $e) {
                $io->error("Error en descarga de portada: " . $e->getMessage());
                continue;
            }

            // Extraer enlaces usando lógica del scraper
            $links = [];
            if ($scraper instanceof AbstractHtmlScraper) {
                $io->text("Extrayendo enlaces mediante selectores del scraper...");
                $links = $scraper->extractArticleLinks($htmlPortada, 5);
            } else {
                $io->warning("El scraper no hereda de AbstractHtmlScraper");
            }

            if (empty($links)) {
                $io->warning("No se encontraron enlaces válidos en la portada.");
                continue;
            }

            // Descargar detalles de los artículos
            foreach ($links as $index => $linkUrl) {
                $io->text("Descargando detalle ($index): $linkUrl");

                try {
                    $detailHtml = $this->fetchContent($linkUrl);
                    // Guardado secuencial: article_0.html, article_1.html...
                    file_put_contents($targetDir . "/article_{$index}.html", $detailHtml);
                } catch (\Exception $e) {
                    $io->warning("Error descargando detalle: " . $e->getMessage());
                    // Generar archivo de error para evitar fallo en tests
                    file_put_contents($targetDir . "/article_{$index}.html", "<html><body>Error descargando contenido</body></html>");
                }

                // Prevenir bloqueo por rate limit
                usleep(200000);
            }

            $io->newLine();
        }

        $io->success('Terminado');
        return Command::SUCCESS;
    }

    private function fetchContent(string $url): string
    {
        $response = $this->client->request('GET', $url, [
            'timeout' => 10,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
            ]
        ]);

        return $response->getContent();
    }
}
