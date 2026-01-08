<?php

namespace App\Domain\Entity;

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

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $title = null;

    #[ORM\Column(length: 500)]
    #[Assert\NotBlank]
    #[Assert\Url]
    private ?string $url = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['El Pais', 'El Mundo', 'Manual'])]
    private ?string $source = null; // Podría ser un Enum, lo dejamos string simple por ahora

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $publishedAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $scrapedAt = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)] // TEXT porque puede ser muy largo
    private ?string $body = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $image = null;

    public function __construct()
    {
        $this->scrapedAt = new \DateTimeImmutable();
    }

    // Getters y Setters básicos
    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }
    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }
    public function setUrl(string $url): static
    {
        $this->url = $url;
        return $this;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }
    public function setSource(string $source): static
    {
        $this->source = $source;
        return $this;
    }

    public function getPublishedAt(): ?\DateTimeImmutable
    {
        return $this->publishedAt;
    }
    public function setPublishedAt(?\DateTimeImmutable $publishedAt): static
    {
        $this->publishedAt = $publishedAt;
        return $this;
    }

    public function getScrapedAt(): ?\DateTimeImmutable
    {
        return $this->scrapedAt;
    }
    public function setScrapedAt(\DateTimeImmutable $scrapedAt): static
    {
        $this->scrapedAt = $scrapedAt;
        return $this;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }
    public function setBody(?string $body): static
    {
        $this->body = $body;
        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }
    public function setImage(?string $image): static
    {
        $this->image = $image;
        return $this;
    }
}
