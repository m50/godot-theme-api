<?php

declare(strict_types=1);

namespace GCSS\Godot\Resources;

use GCSS\Godot\Color;

class DynamicFont extends Resource
{
    public float $outlineSize = 0.0;
    public Color $outlineColor;
    public bool $useMipmaps = false;
    public bool $useFilter = false;
    public string $font = '';

    public function __construct(string $font = '')
    {
        $this->outlineColor = new Color();
        $this->font = $font;
    }

    public function setOutlineSize(float $size): static
    {
        $this->outlineSize = $size;
        return $this;
    }

    public function setOutlineColor(Color $color): static
    {
        $this->outlineColor = $color;
        return $this;
    }

    public function setUseMipmaps(bool $enabled): static
    {
        $this->useMipmaps = $enabled;
        return $this;
    }

    public function setUseFilter(bool $enabled): static
    {
        $this->useFilter = $enabled;
        return $this;
    }
}
