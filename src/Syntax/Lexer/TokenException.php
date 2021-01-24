<?php

declare(strict_types=1);

namespace GCSS\Syntax\Lexer;

use Exception;

class TokenException extends Exception
{
    public function __construct(string $token)
    {
        $this->code = 400;
        $this->message = "Unknown token provided.";
    }
}
