<?php

declare(strict_types=1);

namespace GCSS\Tests\Unit;

use GCSS\Godot\Color;
use GCSS\Godot\Nodes\HSlider;
use GCSS\Godot\Nodes\PanelContainer;
use GCSS\Godot\Resources\DynamicFont;
use GCSS\Godot\Resources\StyleBoxFlat;
use GCSS\Godot\Resources\Theme;
use GCSS\Syntax\Lexer\Lexer;
use GCSS\Syntax\Parser\Parser;
use PHPUnit\Framework\TestCase;

class ParserTest extends TestCase
{
    /** @test */
    public function test_basic_parse()
    {
        $input = <<<INPUT
        default-font {
            outline-size: 1;
            outline-color: #000000ff;
            use-mipmaps: true;
            font: "res://Assets/Fonts/OpenDyslexic2/OpenDyslexic-Regular.otf";
        }
        INPUT;
        $expected = new Theme();
        $expected->setFont(
            (new DynamicFont('res://Assets/Fonts/OpenDyslexic2/OpenDyslexic-Regular.otf'))
                ->setOutlineSize(1)
                ->setOutlineColor(new Color('#000000ff'))
                ->setUseMipmaps(true)
        );
        $lexer = new Lexer();
        $parser = new Parser();
        $output = $parser->process($lexer->process($input));
        $this->assertEquals($expected, $output);
    }

    /** @test */
    public function test_parse_file()
    {
        $lexer = new Lexer();
        $parser = new Parser();
        $input = file_get_contents(__DIR__ . '/../fixtures/theme.gcss');

        $expected = new Theme();
        $expected->setFont(
            (new DynamicFont('res://Assets/Fonts/OpenDyslexic2/OpenDyslexic-Regular.otf'))
                ->setOutlineSize(1)
                ->setOutlineColor(new Color('#000000ff'))
                ->setUseMipmaps(true)
                ->setUseFilter(true)
        );
        $expected->addNode(
            (new HSlider())->setIcons([
                'grabber' => 'res://Assets/UserInterface/UIElements/Slider.png',
                'grabber-disabled' => 'res://Assets/UserInterface/UIElements/SliderDisabled.png',
                'grabber-highlight' => 'res://Assets/UserInterface/UIElements/SliderHighlight.png',
            ])
        );
        $expected->addNode(
            (new PanelContainer())->setStyle('panel', new StyleBoxFlat([
                'content-margin-left' => 60.0,
                'content-margin-right' => 60.0,
                'content-margin-top' => 20.0,
                'content-margin-bottom' => 20.0,
                'bg-color' => '#ebb57b',
                'border-width-left' => 8,
                'border-width-top' => 8,
                'border-width-right' => 8,
                'border-width-bottom' => 8,
                'border-color' => '#7d3833',
                'border-blend' => true,
                'corner-radius-top-left' => 8,
                'corner-radius-top-right' => 8,
                'corner-radius-bottom-right' => 8,
                'corner-radius-bottom-left' => 8,
            ]))
        );

        $output = $parser->process($lexer->process($input));
        $this->assertEquals($expected, $output);
    }
}
