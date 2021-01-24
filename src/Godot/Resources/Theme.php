<?php

declare(strict_types=1);

namespace GCSS\Godot\Resources;

use GCSS\Godot\Nodes\Control;

class Theme extends Resource
{
    private ?Font $defaultFont = null;

    /** @var list<\GCSS\Godot\Nodes\Control> */
    private array $nodes = [];

    public function setFont(Font $font): Theme
    {
        $this->defaultFont = $font;
        return $this;
    }

    public function getFont(): ?Font
    {
        return $this->defaultFont;
    }

    public function addNode(Control $node): Theme
    {
        $this->nodes[] = $node;
        return $this;
    }

    /**
     * @return list<\GCSS\Godot\Nodes\Control>
     */
    public function getNodes(): array
    {
        return $this->nodes;
    }
}
