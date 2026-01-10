<?php

namespace App\Command;

use App\Application\UseCase\Feed\SyncDailyNews;
use App\Domain\Repository\FeedRepositoryInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:sync-news',
    description: 'Descarga noticias de los scrapers configurados y las guarda en BD',
)]
class SyncNewsCommand extends Command
{
    public function __construct(
        private SyncDailyNews $syncDailyNews,
        private FeedRepositoryInterface $feedRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        // Añadimos una opción para poder ejecutar: php bin/console app:sync-news --limit=10
        $this->addOption('limit', null, InputOption::VALUE_OPTIONAL, 'Número de noticias por fuente', 5);
        $this->addOption('source', null, InputOption::VALUE_OPTIONAL, 'Fuente específica', null);
        $this->addOption('truncate', null, InputOption::VALUE_NONE, 'Trunca la tabla de noticias');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $limit = (int) $input->getOption('limit');
        $source = $input->getOption('source');
        $truncate = $input->getOption('truncate');

        $io->title('Iniciando Sincronización de Noticias...');

        try {

            if ($truncate) {
                $this->feedRepository->truncate();
                $io->note('Tabla de noticias truncada.');
            }

            $this->syncDailyNews->execute($limit, $source);
            $io->success('Sincronización terminada.');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Ocurrió un error crítico: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
