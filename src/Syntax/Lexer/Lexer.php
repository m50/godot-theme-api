<?php

declare(strict_types=1);

namespace GCSS\Syntax\Lexer;

class Lexer
{
    private const OP_STRING = '"';
    private const OP_OPEN_COMMENT = '/*';
    private const OP_CLOSE_COMMENT = '*/';
    private const OP_COLOR = '#';
    private const V_NULL = 'null';
    private const V_TRUE = 'true';
    private const V_FALSE = 'false';

    public function process(string $input): \Generator
    {
        $chars = \str_split($input);
        $idx = 0;
        $line = 1;
        $column = 1;
        while (isset($chars[$idx])) {
            $char = $chars[$idx];
            $idx++;
            switch (true) {
                case ctype_space($char):
                    if ($char === "\n") {
                        $line++;
                        $column = 1;
                    }
                    break;
                case str_contains('>:', $char):
                    yield [Token::OPERATION(), $char];
                    break;
                case str_contains('{};', $char):
                    yield [Token::fromSpecialCharacter($char), ''];
                    break;
                case "{$char}{$chars[$idx]}" === self::OP_OPEN_COMMENT:
                    $this->scanComment($idx, $chars);
                    break;
                case $char === self::OP_STRING:
                    yield [Token::T_STRING(), $this->scanString($idx, $chars)];
                    break;
                case (bool)preg_match('/[\.0-9]/', $char):
                    yield [Token::T_NUMBER(), $this->scanExpr($idx, $chars, '/[\.0-9]/')];
                    break;
                case $char === self::OP_COLOR:
                    yield [Token::T_COLOR(), $this->scanExpr($idx, $chars, '/[#0-9a-f]/i')];
                    break;
                case (bool)preg_match('/[a-z]/i', $char):
                    $symbol = $this->scanExpr($idx, $chars, '/[a-zA-Z0-9\-]/');
                    if (\strtolower($symbol) === self::V_NULL) {
                        yield [Token::T_NULL(), ''];
                    } elseif (\in_array(\strtolower($symbol), [self::V_TRUE, self::V_FALSE], true)) {
                        yield [TOKEN::T_BOOLEAN(), $symbol];
                    } else {
                        yield [Token::SYMBOL(), $symbol];
                    }
                    break;
                default:
                    throw new LexException($char, $line, $column);
            }
            $column++;
        }
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
        while (isset($chars[$idx]) && $chars[$idx] !== self::OP_STRING) {
            $ret .= $chars[$idx];
            $idx++;
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
