<?php

declare(strict_types=1);

namespace GCSS\Godot\Nodes;

use GCSS\Exceptions\ValueException;
use GCSS\Godot\Resources\StyleBox;
use ReflectionClass;

abstract class Control
{
    /** @var array{panel:StyleBox|null} */
    public array $styles = [
        'panel' => null,
    ];

    public function setStyle(string $key, StyleBox $value): static
    {
        if (! \array_key_exists($key, $this->styles)) {
            throw new ValueException(self::class, "styles/{$key}");
        }
        $this->styles[$key] = $value;

        return $this;
    }

    /**
     * @param array<string,\GCSS\Godot\Resources\StyleBox|null> $styles
     * @return static
     */
    public function setStyles(array $styles): static
    {
        foreach ($styles as $key => $value)
        {
            if (\is_null($value)) {
                continue;
            }
            if (! \array_key_exists($key, $this->styles)) {
                throw new ValueException(static::class, "styles/{$key}");
            }
            $this->styles[$key] = $value;
        }

        return $this;
    }
}
