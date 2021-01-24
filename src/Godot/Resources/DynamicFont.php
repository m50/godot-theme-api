<?php

declare(strict_types=1);

namespace GCSS\Godot\Resources;

use GCSS\Godot\Color;

class DynamicFont extends Font
{
    public float $outlineSize = 0.0;
    public Color $outlineColor;
    public bool $useMipmaps = false;
    public bool $useFilter = false;
    public DynamicFontData $font;

    public function __construct(string $path = '')
    {
        $this->outlineColor = new Color();
        $this->font = new DynamicFontData($path);
    }

    public function setFont(string $path): static
    {
        $this->font = new DynamicFontData($path);
        return $this;
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
