<?php

namespace App\Application\DTO\Request\Feed;

use Symfony\Component\Validator\Constraints as Assert;

readonly class ListFeedsRequest
{
    public function __construct(
        #[Assert\Positive]
        public int $page = 1,

        #[Assert\Range(min: 1, max: 100)]
        public int $limit = 10,

        #[Assert\Choice(['publishedAt', 'title', 'source'])]
        public string $sortBy = 'publishedAt',

        #[Assert\Choice(['ASC', 'DESC'])]
        public string $sortOrder = 'DESC'
    ) {
    }
}