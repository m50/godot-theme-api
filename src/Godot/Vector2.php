<?php

declare(strict_types=1);

namespace GCSS\Godot;

use GCSS\Contracts\TRes;

class Vector2 implements TRes
{
    public function __construct(public float $x = 0, public float $y = 0){}

    public function toTresString(): string
    {
        return "Vector2( {$this->x}, {$this->y} )";
    }
}
