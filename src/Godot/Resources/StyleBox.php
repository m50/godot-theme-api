<?php

declare(strict_types=1);

namespace GCSS\Godot\Resources;

use GCSS\Exceptions\ValueException;
use GCSS\Godot\Color;
use Underscore\Types\Strings;
use GCSS\Contracts\SubResource;

abstract class StyleBox extends Resource implements SubResource
{
    /**
     * @param array<string,float|bool|Color|string|null> $options
     */
    public function __construct(array $options)
    {
        foreach ($options as $key => $value) {
            if (\is_null($value)) {
                continue;
            }
            $key = Strings::toCamelCase($key);
            $method = 'set' . Strings::toPascalCase($key);
            if (! \property_exists(static::class, $key) || ! \method_exists(static::class, $method)) {
                dump($key, $method);
                $key = Strings::toSnakeCase($key);
                throw new ValueException(static::class, $key);
            }
            $this->$method($value);
        }
    }
}
