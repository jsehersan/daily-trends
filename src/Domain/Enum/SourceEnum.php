<?php
namespace App\Domain\Enum;

enum SourceEnum: string
{
    case EL_PAIS = 'El Pais';
    case EL_MUNDO = 'El Mundo';
    //Para lo que entra por api
    case MANUAL = 'Manual';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}