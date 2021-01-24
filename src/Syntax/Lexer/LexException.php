<?php

declare(strict_types=1);

namespace GCSS\Syntax\Lexer;

use Exception;

class LexException extends Exception
{
    public function __construct(string $char, int $line, int $column)
    {
        $this->code = 400;
        $this->message = "Found invalid token `{$char}` on line {$line}, column {$column}.";
    }
}
