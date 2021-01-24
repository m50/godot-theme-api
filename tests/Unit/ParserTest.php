<?php

declare(strict_types=1);

namespace GCSS\Tests\Unit;

use GCSS\Godot\Color;
use GCSS\Godot\Resources\DynamicFont;
use GCSS\Syntax\Lexer\Lexer;
use GCSS\Syntax\Parser\Parser;
use PHPUnit\Framework\TestCase;

class ParserTest extends TestCase
{
    /** @test */
    public function test_basic_parse()
    {
        $input = <<<INPUT
        /* test */
        default-font {
            outline-size: 1;
            outline-color: #000000ff;
            use-mipmaps: true;
            font: "res://Assets/Fonts/OpenDyslexic2/OpenDyslexic-Regular.otf";
        }
        INPUT;
        $expected = [
            "default-font" => (new DynamicFont('res://Assets/Fonts/OpenDyslexic2/OpenDyslexic-Regular.otf'))
                ->setOutlineSize(1)
                ->setOutlineColor(new Color('#000000ff'))
                ->setUseMipmaps(true),
        ];
        $lexer = new Lexer();
        $parser = new Parser();
        $output = iterator_to_array($parser->process($lexer->process($input)));
        $this->assertEquals($expected, $output);
    }
}
