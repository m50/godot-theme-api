<?php

declare(strict_types=1);

namespace GCSS\Tests;

use GCSS\Syntax\Lexer\Lexer;
use GCSS\Syntax\Lexer\LexException;
use GCSS\Syntax\Lexer\Token;
use PHPUnit\Framework\TestCase;

class LexerTest extends TestCase
{
    /** @test */
    public function test_color_lex()
    {
        $input = '#ffffff00';
        $expected = [[Token::T_COLOR(), '#ffffff00', '1:1']];
        $this->assertLexer($input, $expected);
    }

    /** @test */
    public function test_multiline_comment()
    {
        $input = <<<INPUT
        /*
         test
        */
        INPUT;
        $lexer = new Lexer();
        $output = $lexer->process($input);
        $this->assertEmpty($output);
    }

    /** @test */
    public function test_single_line_comment()
    {
        $input = <<<INPUT
        // test
        ;
        INPUT;
        $expected = [[Token::STATEMENT_TERMINATOR(), '', '2:1']];
        $this->assertLexer($input, $expected);
    }

    /** @test */
    public function test_basic_lex()
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
            [Token::SYMBOL(), 'default-font', '2:1'],
            [Token::BLOCK_START(), '', '2:14'],

            [Token::SYMBOL(), 'outline-size', '3:5'],
            [Token::OPERATION(), ':', '3:17'],
            [Token::T_NUMBER(), '1', '3:19'],
            [Token::STATEMENT_TERMINATOR(), '', '3:20'],

            [Token::SYMBOL(), 'outline-color', '4:5'],
            [Token::OPERATION(), ':', '4:18'],
            [Token::T_COLOR(), '#000000ff', '4:20'],
            [Token::STATEMENT_TERMINATOR(), '', '4:29'],

            [Token::SYMBOL(), 'use-mipmaps', '5:5'],
            [Token::OPERATION(), ':', '5:16'],
            [Token::T_BOOLEAN(), 'true', '5:18'],
            [Token::STATEMENT_TERMINATOR(), '', '5:22'],

            [Token::SYMBOL(), 'font', '6:5'],
            [Token::OPERATION(), ':', '6:9'],
            [Token::T_STRING(), 'res://Assets/Fonts/OpenDyslexic2/OpenDyslexic-Regular.otf', '6:11'],
            [Token::STATEMENT_TERMINATOR(), '', '6:70'],

            [Token::BLOCK_END(), '', '7:1'],
        ];

        $this->assertLexer($input, $expected);
    }

    /** @test */
    public function test_lex_error()
    {
        $lexer = new Lexer();
        $input = "''";
        $this->expectException(LexException::class);
        $this->expectExceptionCode(400);
        $this->expectExceptionMessage("Found invalid token `'` on line 1, column 1.");
        $lexer->process($input);
    }

    /** @test */
    public function test_unexpected_end_of_file()
    {
        $lexer = new Lexer();
        $input = '"blah';
        $this->expectException(LexException::class);
        $this->expectExceptionCode(400);
        $this->expectExceptionMessage("Unexpected end of file. Did you properly close your blocks?");
        $lexer->process($input);
    }

    /** @test */
    public function test_lex_works_on_1_character_file()
    {
        $input = ";";
        $expected = [[Token::STATEMENT_TERMINATOR(), '', '1:1']];
        $this->assertLexer($input, $expected);
    }

    /** @test */
    public function test_no_failure_reading_sample_file()
    {
        $lexer = new Lexer();
        $input = file_get_contents(__DIR__ . '/../fixtures/theme.gcss');
        foreach ($lexer->process($input) as $token) {
            $this->assertIsArray($token);
        }
    }


    private function assertLexer(string $input, array $expected): void
    {
        $lexer = new Lexer();
        foreach ($lexer->process($input) as $idx => $token) {
            $this->assertEquals($expected[$idx], $token, "Lexer output doesn't match expected value.");
        }
    }
}
