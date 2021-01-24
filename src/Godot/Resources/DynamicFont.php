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
}
