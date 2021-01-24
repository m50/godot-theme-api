<?php

declare(strict_types=1);

namespace GCSS\Godot\Nodes;

use GCSS\Godot\Resources\StyleBox;

class PanelContainer extends Control
{
    /** @var array{panel:StyleBox|null} */
    public array $styles = [
        'panel' => null,
    ];
}
