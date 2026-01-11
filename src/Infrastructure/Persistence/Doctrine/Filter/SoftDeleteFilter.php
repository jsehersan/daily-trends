<?php

namespace App\Infrastructure\Persistence\Doctrine\Filter;

use App\Domain\Contract\SoftDeletableInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

class SoftDeleteFilter extends SQLFilter
{
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias): string
    {
        //Si la entidad implementa la intefaz, lo aplicamos.
        if (!$targetEntity->getReflectionClass()->implementsInterface(SoftDeletableInterface::class)) {
            return '';
        }

        return sprintf('%s.deleted_at IS NULL', $targetTableAlias);
    }
}