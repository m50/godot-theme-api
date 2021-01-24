<?php

declare(strict_types=1);

namespace GCSS\Godot\Resources;

use GCSS\Contracts\ExternalResource;

class DynamicFontData extends Resource implements ExternalResource
{
    public function __construct(public string $path)
    {
    }
}
