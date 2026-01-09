<?php

namespace App\Domain\Entity;

use App\Domain\Enum\SourceEnum;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'feeds')]
#[ORM\UniqueConstraint(name: 'unique_source_url', columns: ['source', 'url'])]

class Feed
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $scrapedAt;

    public function __construct(
        #[ORM\Column(length: 255)]
        #[Assert\NotBlank]
        private string $title,

        #[ORM\Column(length: 500)]
        #[Assert\NotBlank]
        #[Assert\Url]
        private string $url,

        #[ORM\Column(length: 50, enumType: SourceEnum::class)]
        #[Assert\NotNull]
        private SourceEnum $source,

        #[ORM\Column(type: Types::TEXT)]
        #[Assert\NotBlank]
        private string $body,

        #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
        #[Assert\NotNull]
        private \DateTimeImmutable $publishedAt,

        #[ORM\Column(length: 500, nullable: true)]
        private ?string $image = null,
    ) {
        $this->scrapedAt = new \DateTimeImmutable();
    }


    public function getId(): ?Uuid
    {
        return $this->id;
    }
    public function getTitle(): string
    {
        return $this->title;
    }
    public function getUrl(): string
    {
        return $this->url;
    }
    public function getSource(): SourceEnum
    {
        return $this->source;
    }
    public function getBody(): string
    {
        return $this->body;
    }
    public function getPublishedAt(): \DateTimeImmutable
    {
        return $this->publishedAt;
    }
    public function getImage(): ?string
    {
        return $this->image;
    }
    public function getScrapedAt(): \DateTimeImmutable
    {
        return $this->scrapedAt;
    }
}