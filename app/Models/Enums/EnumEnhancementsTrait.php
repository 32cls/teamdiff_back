<?php

declare(strict_types=1);

namespace App\Models\Enums;

trait EnumEnhancementsTrait
{
    /**
     * Returns enum values as an array.
     */
    public static function valueArray(): array
    {
        foreach (self::cases() as $enum) {
            $values[] = $enum->value ?? $enum->name;
        }

        return $values;
    }

    /**
     * Returns enum values as a list.
     */
    public static function valueList(string $separator = ', '): string
    {
        return implode($separator, self::valueArray());
    }
}
