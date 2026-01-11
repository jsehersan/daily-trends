<?php

namespace App\Domain\Contract;
// El filtro de doctrine valida que el entity lleve esta interface, lo hacemos más seguro
// que simplemente comproblando que tenga la la columna deletedAt
interface SoftDeletableInterface
{
    public function getDeletedAt(): ?\DateTimeImmutable;
}