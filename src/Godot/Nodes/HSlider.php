<?php

declare(strict_types=1);

namespace GCSS\Godot\Nodes;

use GCSS\Godot\Resources\StyleBox;

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
}
