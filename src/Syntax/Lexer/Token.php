<?php

declare(strict_types=1);

namespace GCSS\Syntax\Lexer;

use MyCLabs\Enum\Enum;

/**
 * @method static Token T_NUMBER()
 * @method static Token T_STRING()
 * @method static Token T_COLOR()
 * @method static Token T_NULL()
 * @method static Token T_BOOLEAN()
 * @method static Token SYMBOL()
 * @method static Token OPERATION()
 * @method static Token STATEMENT_TERMINATOR()
 * @method static Token BLOCK_START()
 * @method static Token BLOCK_END()
 */
class Token extends Enum
{
    // Types
    private const T_NUMBER = 'number';
    private const T_STRING = 'string';
    private const T_COLOR = 'color';
    private const T_NULL = 'null';
    private const T_BOOLEAN = 'boolean';

    // Others
    private const SYMBOL = 'symbol';
    private const OPERATION = 'operation';
    private const STATEMENT_TERMINATOR = ';';
    private const BLOCK_START = '{';
    private const BLOCK_END = '}';

    public static function fromSpecialCharacter(string $char): static
    {
        if ($char === ';') {
            return static::STATEMENT_TERMINATOR();
        } elseif ($char === '{') {
            return static::BLOCK_START();
        } elseif ($char === '}') {
            return static::BLOCK_END();
        }
    }

    public function __toString()
    {
        return $this->value;
    }
}
