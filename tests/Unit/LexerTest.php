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
        $expected = [[Token::T_COLOR(), '#ffffff00']];
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
            [Token::SYMBOL(), 'default-font'],
            [Token::BLOCK_START(), ''],

            [Token::SYMBOL(), 'outline-size'],
            [Token::OPERATION(), ':'],
            [Token::T_NUMBER(), '1'],
            [Token::STATEMENT_TERMINATOR(), ''],

            [Token::SYMBOL(), 'outline-color'],
            [Token::OPERATION(), ':'],
            [Token::T_COLOR(), '#000000ff'],
            [Token::STATEMENT_TERMINATOR(), ''],

            [Token::SYMBOL(), 'use-mipmaps'],
            [Token::OPERATION(), ':'],
            [Token::T_BOOLEAN(), 'true'],
            [Token::STATEMENT_TERMINATOR(), ''],

            [Token::SYMBOL(), 'font'],
            [Token::OPERATION(), ':'],
            [Token::T_STRING(), 'res://Assets/Fonts/OpenDyslexic2/OpenDyslexic-Regular.otf'],
            [Token::STATEMENT_TERMINATOR(), ''],
            [Token::BLOCK_END(), ''],
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
        iterator_to_array($lexer->process($input));
    }

    /** @test */
    public function test_lex_works_on_1_character_file()
    {
        $input = ";";
        $expected = [[Token::STATEMENT_TERMINATOR(), '']];
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
