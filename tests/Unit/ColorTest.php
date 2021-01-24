<?php

declare(strict_types=1);

namespace GCSS\Tests\Unit;

use GCSS\Godot\Color;
use GCSS\Godot\Resources\DynamicFont;
use GCSS\Syntax\Lexer;
use GCSS\Syntax\Parser\Parser;
use PHPUnit\Framework\TestCase;

class ColorTest extends TestCase
{
    /**
     * @test
     * @dataProvider colorData
     */
    public function test_color(string $colorStr)
    {
        $color = new Color($colorStr);
        $this->assertEquals(0, $color->r, "Red isn't correct.");
        $this->assertEquals(1, $color->g, "Green isn't correct.");
        $this->assertEquals(0, $color->b, "Blue isn't correct.");
        $this->assertEquals(1, $color->a, "Alpha isn't correct.");
        $this->assertEquals('Color( 0, 1, 0, 1 )', $color->toTresString());
    }

    public function colorData(): array
    {
        return [
            'long with alpha' => ['#00ff00ff'],
            'long without alpha' => ['#00ff00'],
            'short with alpha' => ['#0f0f'],
            'short without alpha' => ['#0f0'],
        ];
    }
}
