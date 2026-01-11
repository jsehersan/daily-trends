<?php

namespace App\Application\DTO\Request\Feed;

use App\Domain\Enum\SourceEnum;
use Symfony\Component\Validator\Constraints as Assert;

readonly class UpdateFeedRequest
{
    public function __construct(
        #[Assert\Length(min: 5, max: 255)]
        public ?string $title = null,

        #[Assert\Url(requireTld: true)]
        public ?string $url = null,

        public ?string $body = null,

        #[Assert\Choice(
            callback: [SourceEnum::class, 'values'],
            message: "La fuente '{{ value }}' no es válida. Las opciones posibles son: {{ choices }}"
        )]
        public ?string $source = null,

        #[Assert\DateTime(format: 'Y-m-d H:i:s')]
        public ?string $publishedAt = null,

        #[Assert\Url(requireTld: true)]
        public ?string $image = null,

        // Para indicar el borrado de la imagen
        public bool $removeImage = false,
    ) {
    }
}