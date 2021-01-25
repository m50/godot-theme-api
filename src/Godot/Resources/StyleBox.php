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
            if (! \property_exists(get_called_class(), $key) || ! \method_exists(get_called_class(), $method)) {
                dump($key, $method);
                $key = Strings::toSnakeCase($key);
                throw ValueException::missing(get_called_class(), $key);
            }
            $this->$method($value);
        }
    }
}
