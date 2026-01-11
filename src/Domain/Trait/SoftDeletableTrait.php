<?php

namespace App\Domain\Trait;

use Doctrine\ORM\Mapping as ORM;

trait SoftDeletableTrait
{
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $deletedAt = null;


    public function getDeletedAt(): ?\DateTimeImmutable
    {
        return $this->deletedAt;
    }

    public function isDeleted(): bool
    {
        return null !== $this->deletedAt;
    }

    public function softDelete(): void
    {
        if ($this->deletedAt === null) {
            $this->deletedAt = new \DateTimeImmutable();
        }
    }

    public function recover(): void
    {
        $this->deletedAt = null;
    }
}