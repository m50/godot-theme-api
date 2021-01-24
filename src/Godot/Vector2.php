<?php

declare(strict_types=1);

namespace GCSS\Godot;

use GCSS\Contracts\TRes;

class Vector2 implements TRes
{
    public float $x;
    public float $y;

    public function __construct(float $x = 0, float $y = 0)
    {
        $this->x = $x;
        $this->y = $y;
    }

    public function toTresString(): string
    {
        return "Vector2( {$this->x}, {$this->y} )";
    }
}
