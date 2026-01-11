<?php

namespace App\Domain\Entity;

use App\Domain\Enum\SourceEnum;
use App\Domain\Trait\TimestampableTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'feeds')]
#[ORM\UniqueConstraint(name: 'unique_source_url', columns: ['source', 'url'])]

class Feed
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id = null;

    //Usamos un trait para dejar mas limpio la entity y darle la capacidad de tener timestamps 
    use TimestampableTrait;

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

        #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
        private ?\DateTimeImmutable $scrapedAt = null,
    ) {

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
    public function getScrapedAt(): ?\DateTimeImmutable
    {
        return $this->scrapedAt;
    }

    //Mantenemos el original si es null lo que viene
    public function update(
        ?string $title = null,
        ?string $url = null,
        ?string $body = null,
        ?SourceEnum $source = null,
        ?\DateTimeImmutable $publishedAt = null,
        ?string $image = null,
        bool $removeImage = false
    ): void {
        $this->title = $title ?? $this->title;
        $this->url = $url ?? $this->url;
        $this->body = $body ?? $this->body;
        $this->source = $source ?? $this->source;
        $this->publishedAt = $publishedAt ?? $this->publishedAt;

        // Gestión de imagen
        if ($removeImage) {
            $this->image = null;
        } elseif ($image !== null) {
            $this->image = $image;
        }
    }
}