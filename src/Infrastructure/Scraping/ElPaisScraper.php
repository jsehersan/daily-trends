<?php

namespace App\Infrastructure\Scraping;

use App\Domain\Enum\SourceEnum;

class ElPaisScraper extends AbstractHtmlScraper
{
    public function getSource(): SourceEnum
    {
        return SourceEnum::EL_PAIS;
    }

    protected function getUrl(): string
    {
        return 'https://elpais.com';
    }

    protected function getSelectors(): array
    {
        return [
            'article_link' => 'article h2 > a, article h2 a, h2.c_t a',

            'title' => [
                'h1',
                '.a_t',
                'article header h2'
            ],
            'body' => [
                '.a_c p',
                'article[data-dtm-region="articulo_cuerpo"] p',
                'article p'
            ],
            'image' => [
                'article img[src^="http"]',
                'figure img',
                '.a_m img'
            ],
            'date' => [
                'time',
                '.a_ti',
                '[data-date]'
            ]
        ];
    }
}