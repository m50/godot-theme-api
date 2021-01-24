<?php

declare(strict_types=1);

namespace GCSS\Godot\Nodes;

class PanelContainer extends Control
{
    /** @var array{panel:StyleBox|null} */
    public array $styles = [
        'panel' => null,
    ];
}
