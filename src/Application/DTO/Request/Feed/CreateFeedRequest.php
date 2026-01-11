<?php

namespace App\Application\DTO\Request\Feed;

use App\Domain\Enum\SourceEnum;
use Symfony\Component\Validator\Constraints as Assert;

readonly class CreateFeedRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(min: 5, max: 255)]
        public string $title,

        #[Assert\NotBlank]
        #[Assert\Url(requireTld: true)]
        public string $url,

        #[Assert\NotBlank]
        public string $body,

            //No necesario puesto que lo marcaremos como MANUAL siempre que entre por la api, tiene más lógica
            //#[Assert\NotBlank] 
            // #[Assert\Choice(
            //     callback: [SourceEnum::class, 'values'],
            //     message: "Source '{{ value }}' is not valid. Available options: {{ choices }}"
            // )]
            //public string $source,

        #[Assert\NotBlank]
        #[Assert\DateTime(format: 'Y-m-d H:i:s', message: "Invalid date, must be this format Y-m-d H:i:s")]
        public string $publishedAt,

        #[Assert\Url(requireTld: true)]
        public ?string $image = null,
    ) {
    }
}