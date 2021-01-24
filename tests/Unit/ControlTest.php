<?php

declare(strict_types=1);

namespace GCSS\Tests\Unit;

use GCSS\Godot\Nodes\HSlider;
use PHPUnit\Framework\TestCase;
use GCSS\Exceptions\ValueException;
use GCSS\Godot\Resources\StyleBoxFlat;
use GCSS\Godot\Resources\StyleBox;

class ControlTest extends TestCase
{
    /** @test */
    public function test_style_set()
    {
        $control = new HSlider();
        $control->setStyle('slider', new StyleBoxFlat());
        $this->assertInstanceOf(StyleBox::class, $control->styles['slider']);
    }

    /** @test */
    public function test_invalid_style_set()
    {
        $this->expectExceptionMessage("HSlider/styles/not-existant doesn't exist");
        $this->expectException(ValueException::class);
        $control = new HSlider();
        $control->setStyle('not-existant', new StyleBoxFlat());
    }
}
