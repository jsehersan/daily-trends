<?php

namespace App\Domain\Contract;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.news_scraper')]
interface NewsScraperInterface
{
    /**
     * @return array<\App\Domain\Entity\Feed>
     */
    public function scrape(int $limit = 5): array; //Si cambian los requerimientos a más feeds, valorar un Paginate. 
    public function getSource(): string;
}
