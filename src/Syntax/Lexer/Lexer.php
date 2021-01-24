<?php

declare(strict_types=1);

namespace GCSS\Syntax\Lexer;

use Exception;
use GCSS\Syntax\Parser\ParseException;
use Underscore\Parse;

class Lexer
{
    private const OP_STRING = '"';
    private const OP_SINGLELINE_COMMENT = '//';
    private const OP_OPEN_COMMENT = '/*';
    private const OP_CLOSE_COMMENT = '*/';
    private const OP_COLOR = '#';
    private const V_NULL = 'null';
    private const V_TRUE = 'true';
    private const V_FALSE = 'false';

    /**
     * Lexes the input string to create a token tree.
     *
     * @param string $input The GCSS code to be lexed.
     * @return list<array{0:Token,1:string,2:string}>
     */
    public function process(string $input): array
    {
        $ret = [];
        $chars = \str_split($input);
        $idx = 0;
        $line = 1;
        $column = 1;
        while (isset($chars[$idx])) {
            $char = $chars[$idx];
            $idx++;
            $prevIdx = $idx;
            switch (true) {
                case \ctype_space($char):
                    if ($char === "\n") {
                        $line++;
                        $column = 0;
                    }
                    break;
                case \in_array($char, ['>', ':'], true):
                    $ret[] = [Token::OPERATION(), $char, "{$line}:{$column}"];
                    break;
                case \in_array($char, ['{', '}', ';'], true):
                    $ret[] = [Token::fromSpecialCharacter($char), '', "{$line}:{$column}"];
                    break;
                case "{$char}{$chars[$idx]}" === self::OP_SINGLELINE_COMMENT:
                    $this->scanExpr($idx, $chars, '/[^\n]/');
                    break;
                case "{$char}{$chars[$idx]}" === self::OP_OPEN_COMMENT:
                    $this->scanComment($idx, $chars, "{$line}:{$column}");
                    break;
                case $char === self::OP_STRING:
                    $ret[] = [Token::T_STRING(), $this->scanString($idx, $chars), "{$line}:{$column}"];
                    break;
                case (bool)preg_match('/[\.0-9]/', $char):
                    $ret[] = [
                        Token::T_NUMBER(),
                        $this->scanExpr($idx, $chars, '/[\.0-9]/'),
                        "{$line}:{$column}"
                    ];
                    break;
                case $char === self::OP_COLOR:
                    $ret[] = [
                        Token::T_COLOR(),
                        $this->scanExpr($idx, $chars, '/[#0-9a-f]/i'),
                        "{$line}:{$column}"
                    ];
                    break;
                case (bool)preg_match('/[a-z]/i', $char):
                    $symbol = $this->scanExpr($idx, $chars, '/[a-zA-Z0-9\-]/');
                    if (\strtolower($symbol) === self::V_NULL) {
                        $ret[] = [Token::T_NULL(), '', "{$line}:{$column}"];
                    } elseif (\in_array(\strtolower($symbol), [self::V_TRUE, self::V_FALSE], true)) {
                        $ret[] = [Token::T_BOOLEAN(), $symbol, "{$line}:{$column}"];
                    } else {
                        $ret[] = [Token::SYMBOL(), $symbol, "{$line}:{$column}"];
                    }
                    break;
                default:
                    throw new LexException($char, $line, $column);
            }
            $column += $idx + 1 - $prevIdx;
        }

        return $ret;
    }

    /**
     * Scans forward until the regular expression no longer matches.
     *
     * @param integer $idx
     * @param list<string> $chars
     * @param string $expr
     * @return string
     */
    private function scanExpr(int &$idx, array $chars, string $expr): string
    {
        $ret = '';
        $idx--;
        while (isset($chars[$idx]) && \preg_match($expr, $chars[$idx])) {
            $ret .= $chars[$idx];
            $idx++;
        }

        return $ret;
    }

    /**
     * Scans for the entirety of a string.
     *
     * @param integer $idx
     * @param list<string> $chars
     * @return string
     */
    private function scanString(int &$idx, array $chars): string
    {
        $ret = '';
        while ($chars[$idx] !== self::OP_STRING) {
            $ret .= $chars[$idx];
            $idx++;
            if (! isset($chars[$idx])) {
                throw LexException::unexpectedEndOfFile();
            }
        }
        $idx++;

        return $ret;
    }

    /**
     * Scan out a comment, essentially removing it from the lexing.
     * Since this doesn't return anything, it essentially removes it.
     *
     * @param integer $idx
     * @param list<string> $chars
     * @return void
     */
    private function scanComment(int &$idx, array $chars): void
    {
        while (
            isset($chars[$idx]) && isset($chars[$idx+1]) &&
            $chars[$idx] . $chars[$idx+1] !== self::OP_CLOSE_COMMENT
        ) {
            $idx++;
        }
        // Bump index 2 more times, to skip past the `*/`
        $idx++;
        $idx++;
    }
}
