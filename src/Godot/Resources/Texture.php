<?php

declare(strict_types=1);

namespace GCSS\Godot\Resources;

use GCSS\Contracts\ExternalResource;

class Texture extends Resource implements ExternalResource
{
    public function __construct(private string $path)
    {
        $this->path = $path;
    }

    public function getPath(): string
    {
        return $this->path;
    }
}
