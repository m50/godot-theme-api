<?php

declare(strict_types=1);

namespace GCSS\Godot\Resources;

class Texture extends Resource
{
    public string $path = '';

    public function __construct(string $path)
    {
        $this->path = $path;
    }
}
