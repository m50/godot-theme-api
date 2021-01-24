<?php

declare(strict_types=1);

namespace GCSS\Syntax;

use GCSS\Syntax\Lexer\Lexer;
use GCSS\Syntax\Parser\Parser;

class Transpiler
{
    private Lexer $lexer;
    private Parser $parser;

    public function __construct(Lexer $lexer, Parser $parser)
    {
        $this->lexer = $lexer;
        $this->parser = $parser;
    }

    public function execute(string $input): string
    {
        $tokens = $this->lexer->process($input);
        $parsedObjects = $this->parser->process($tokens);
        return '';
    }
}
