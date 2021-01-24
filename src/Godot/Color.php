<?php

declare(strict_types=1);

namespace GCSS\Godot;

use GCSS\Contracts\TRes;

class Color implements TRes
{
    public float $r = 0;
    public float $g = 0;
    public float $b = 0;
    public float $a = 0;

    public function __construct(string $hex = '#ffffffff')
    {
        // Convert from hex notation to rgba here.
    }

    public function toTresString(): string
    {
        return "Color( {$this->r}, {$this->g}, {$this->b}, {$this->a} )";
    }
}
