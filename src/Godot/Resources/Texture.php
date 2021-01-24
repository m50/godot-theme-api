<?php

declare(strict_types=1);

namespace GCSS\Godot\Resources;

class Texture extends Resource
{
    public function __construct(public string $path = '') {}
}
