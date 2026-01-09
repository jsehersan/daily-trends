<?php

namespace App\Infrastructure\Scraping;

use App\Domain\Enum\SourceEnum;

class ElMundoScraper extends AbstractHtmlScraper
{
    public function getSource(): SourceEnum
    {
        return SourceEnum::EL_MUNDO;
    }

    protected function getUrl(): string
    {
        return 'https://www.elmundo.es';
    }

    protected function getSelectors(): array
    {
        return [
            'article_link' => 'article:not(.ue-c-cover-content--wgt-s) header a, article:not(.ue-c-cover-content--wgt-s) h2 a',

            'title' => [
                'h1.ue-c-article__headline',
                'h1.ue-c-cover-content__headline',
                'h1'
            ],

            'body' => [
                '.ue-c-article__body p',
                'div[data-section="articleBody"] p',
                'main article p'
            ],
            'image' => [
                '.ue-c-article__media img',
                'article figure img',
                '.ue-c-article__image'
            ],

            'date' => [
                'time',
                '.ue-c-article__publishdate time',
                '.ue-c-article__publishdate'
            ]
        ];
    }
}
