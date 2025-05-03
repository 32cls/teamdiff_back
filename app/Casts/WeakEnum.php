<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

readonly class WeakEnum implements CastsAttributes
{
    public function __construct(
        private string $enumClass,
    ) {
    }

    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     * @return \BackedEnum|mixed
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (is_subclass_of($this->enumClass, \BackedEnum::class)) {
            $try = $this->enumClass::tryFrom($value);
        }
        return $try ?? $value;
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return $value instanceof \BackedEnum
            ? $value->value
            : $value;
    }

    public static function of($class): string
    {
        return static::class.':'.$class;
    }
}
