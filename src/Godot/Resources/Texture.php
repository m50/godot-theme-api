<?php

declare(strict_types=1);

namespace GCSS\Godot\Resources;

use GCSS\Contracts\ExternalResource;

class Texture extends Resource implements ExternalResource
{
    public string $path = '';

    public function __construct(string $path)
    {
        $this->path = $path;
    }
}
