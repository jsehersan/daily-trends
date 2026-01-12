<?php

namespace App\Domain\Entity;

use App\Domain\Contract\SoftDeletableInterface;
use App\Domain\Enum\SourceEnum;
use App\Domain\Trait\SoftDeletableTrait;
use App\Domain\Trait\TimestampableTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB; // <--- Importante

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'feeds')]
#[ORM\UniqueConstraint(name: 'unique_source_url', columns: ['source', 'url'])]
#[MongoDB\Document(collection: "feeds")] 
#[MongoDB\UniqueIndex(keys: ['source' => 'asc', 'url' => 'asc'])]
class Feed implements SoftDeletableInterface
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[MongoDB\Id(strategy: "NONE", type: "uuid")]
    private ?Uuid $id = null;

    use TimestampableTrait, SoftDeletableTrait;

    public function __construct(
        #[ORM\Column(length: 255)]
        #[MongoDB\Field(type: "string")] 
        #[Assert\NotBlank]
        private string $title,

        #[ORM\Column(length: 500)]
        #[MongoDB\Field(type: "string")]
        #[Assert\NotBlank]
        #[Assert\Url]
        private string $url,

        #[ORM\Column(length: 50, enumType: SourceEnum::class)]
        #[MongoDB\Field(type: "string", enumType: SourceEnum::class)] 
        #[Assert\NotNull]
        private SourceEnum $source,

        #[ORM\Column(type: Types::TEXT)]
        #[MongoDB\Field(type: "string")]
        #[Assert\NotBlank]
        private string $body,

        #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
        #[MongoDB\Field(type: "date_immutable")] 
        #[Assert\NotNull]
        private \DateTimeImmutable $publishedAt,

        #[ORM\Column(length: 500, nullable: true)]
        #[MongoDB\Field(type: "string", nullable: true)]
        private ?string $image = null,

        #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
        #[MongoDB\Field(type: "date_immutable", nullable: true)]
        private ?\DateTimeImmutable $scrapedAt = null,
    ) {
        //Si persistimos en Mongo, el ID no puede ser nulo al insertar
        // ya que no usamos la estrategia AUTO de Mongo, sino UUIDs de Symfony.
        $this->id = $this->id ?? Uuid::v4();
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