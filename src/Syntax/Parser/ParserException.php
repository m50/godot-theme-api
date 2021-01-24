<?php

declare(strict_types=1);

namespace GCSS\Syntax\Parser;

use Exception;

class ParserException extends Exception
{
    public static function unknownNode(string $nodeName): ParserException
    {
        return new ParserException("Unknown node '{$nodeName}'", 400);
    }
}
