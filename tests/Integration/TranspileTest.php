<?php

declare(strict_types=1);

namespace GCSS\Tests\Integration;

use GCSS\Syntax\Transpiler;
use GCSS\Syntax\Lexer\Lexer;
use GCSS\Syntax\Parser\Parser;
use PHPUnit\Framework\TestCase;

class TranspileTest extends TestCase
{
    /** @test */
    public function test_transpilation_of_gcss_to_tres()
    {
        $input = file_get_contents(__DIR__ . '/../fixtures/theme.gcss');
        $transpiler = new Transpiler(new Lexer(), new Parser());
        $result = $transpiler->execute($input);
        $this->assertStringEqualsFile(__DIR__ . '/../fixtures/theme.tres', $result);
    }
}
