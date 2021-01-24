<?php

declare(strict_types=1);

namespace GCSS\Godot\Nodes;

use GCSS\Exceptions\ValueException;
use GCSS\Godot\Resources\StyleBox;
use GCSS\Godot\Resources\Texture;

class HSlider extends Control
{
    /**
     * @var array{grabber:Texture|null,grabber-disabled:Texture|null,grabber-highlight:Texture|null,tick:Texture|null}
     */
    public array $icons = [
        'grabber' => null,
        'grabber-disabled' => null,
        'grabber-highlight' => null,
        'tick' => null,
    ];

    /** @var array{grabber-area:StyleBox|null,grabber-area-highlight:StyleBox|null,slider:StyleBox|null} */
    public array $styles = [
        'grabber-area' => null,
        'grabber-area-highlight' => null,
        'slider' => null,
    ];

    /**
     * @param string $key
     * @param \GCSS\Godot\Resources\Texture|string $value
     * @return static
     * @throws \GCSS\Exceptions\ValueException
     */
    public function setIcon(string $key, Texture|string $value): static
    {
        if (\is_string($value)) {
            $value = new Texture($value);
        }
        if (! \array_key_exists($key, $this->icons)) {
            throw new ValueException(static::class, "icons/{$key}");
        }
        $this->icons[$key] = $value;

        return $this;
    }

    /**
     * @param array<string,string|null> $icons
     * @return static
     */
    public function setIcons(array $icons): static
    {
        foreach ($icons as $key => $value) {
            if (\is_null($value)) {
                continue;
            }
            if (! \array_key_exists($key, $this->icons)) {
                throw new ValueException(static::class, "icons/{$key}");
            }
            $this->icons[$key] = new Texture($value);
        }

        return $this;
    }
}
