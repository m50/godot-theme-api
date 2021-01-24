<?php

declare(strict_types=1);

namespace GCSS\Syntax\Parser;

use GCSS\Godot\Color;
use GCSS\Godot\Nodes\Control;
use GCSS\Godot\Resources\DynamicFont;
use GCSS\Godot\Resources\Resource;
use GCSS\Godot\Resources\Theme;
use GCSS\Syntax\Lexer\Token;
use Underscore\Types\Strings;

class Parser
{
    /**
     * Process the tokens from the Lexer and turn it into an object tree.
     *
     * @param list<array{0:\GCSS\Syntax\Lexer\Token,1:string,2:string}> $tokens The output from the Lexer.
     * @return Theme
     */
    public function process(array $tokens): Theme
    {
        $theme = new Theme();

        $state = 'root';

        $idx = 0;
        while (isset($tokens[$idx])) {
            [$token, $value, $location] = $tokens[$idx];
            if ($state === 'root') {
                $state = match (true) {
                    Token::SYMBOL()->equals($token) => $value,
                    default => throw ParseException::invalidSyntax($token, $location),
                };
            } else {
                if (Token::BLOCK_START()->equals($token)) {
                    if ($state === 'default-font') {
                        /** @var \GCSS\Godot\Resources\Font $font */
                        $font = $this->buildObject($idx, $tokens, DynamicFont::class);
                        $theme->setFont($font);
                    } else {
                        $class = $this->validateNode($state, $location);
                        /** @var Control $node */
                        $node = $this->buildObject($idx, $tokens, $class);
                        $theme->addNode($node);
                    }
                    $state = 'root';
                } elseif (Token::OPERATION()->equals($token) && $value === '>') {
                    $idx++;
                    $class = $this->validateNode($state, $location);
                    /** @var Control $node */
                    $node = new $class();
                    $theme->addNode($node);
                    $this->setNextObjectValue($idx, $tokens, $node);
                }
            }
            $idx++;
        }

        return $theme;
    }

    /**
     * Validates a symbol as a Control node
     *
     * @param string $state
     * @param string $value
     * @param string $location
     * @return class-string<Resource>
     */
    private function validateResource(string $state, string $location): string
    {
        /** @var class-string<Resource> $class */
        $class = '\\GCSS\\Godot\\Resources\\' . Strings::toPascalCase($state);
        if (! class_exists($class)) {
            throw ParseException::unknownResource($state, $location);
        }

        return $class;
    }

    /**
     * Validates a symbol as a Control node
     *
     * @param string $state
     * @param string $value
     * @param string $location
     * @return class-string<Control>
     */
    private function validateNode(string $state, string $location): string
    {
        /** @var class-string<Control> $class */
        $class = '\\GCSS\\Godot\\Nodes\\' . Strings::toPascalCase($state);
        if (! class_exists($class)) {
            throw ParseException::unknownNode($state, $location);
        }

        return $class;
    }

    /**
     * @param int $idx
     * @param list<array{0:\GCSS\Syntax\Lexer\Token,1:string,2:string}> $tokens The output from the Lexer.
     * @param class-string<Control>|class-string<Resource> $className
     * @return Control|Resource
     */
    private function buildObject(int &$idx, array $tokens, string $className): Control|Resource
    {
        /** @var Control|Resource $obj */
        $obj = new $className();
        $idx++;
        while (! Token::BLOCK_END()->equals($tokens[$idx][0])) {
            $this->setNextObjectValue($idx, $tokens, $obj);
        }

        return $obj;
    }

    /**
     * @param int $idx
     * @param list<array{0:\GCSS\Syntax\Lexer\Token,1:string,2:string}> $tokens The output from the Lexer.
     * @param \GCSS\Godot\Nodes\Control|\GCSS\Godot\Resources\Resource $obj
     * @return void
     * @throws \GCSS\Syntax\Parser\ParseException
     */
    private function setNextObjectValue(int &$idx, array $tokens, Control|Resource &$obj): void
    {
        [$key, $value, $location] = $this->readStatement($idx, $tokens);
        $set = 'set' . Strings::toPascalCase($key);
        if (! method_exists($obj, $set)) {
            throw ParseException::propertyDoesntExist($obj::class, $key, $location);
        }
        $obj->$set($value);
        $idx++;
    }

    /**
     * @param int $idx
     * @param list<array{0:\GCSS\Syntax\Lexer\Token,1:string,2:string}> $tokens The output from the Lexer.
     * @return array{0:string,1:bool|Color|Resource|float|string|null|array,2:string}
     */
    private function readStatement(int &$idx, array $tokens): array
    {
        $statement = [null, null, null];
        $inAssignment = false;
        [$token] = $tokens[$idx];
        while (
            ! Token::STATEMENT_TERMINATOR()->equals($tokens[$idx][0]) &&
            ! Token::BLOCK_END()->equals($tokens[$idx][0])
        ) {
            [$token, $value, $location] = $tokens[$idx];
            $idx++;
            if (Token::OPERATION()->equals($token) && $value === ':') {
                $inAssignment = true;
            } elseif (Token::BLOCK_START()->equals($token)) {
                $statement[1] = $this->buildArray($idx, $tokens);
            } elseif ($inAssignment) {
                if (Token::SYMBOL()->equals($token)) {
                    /** @var class-string<Resource> $class */
                    $class = $this->validateResource($value, $location);
                    $statement[1] = $this->buildObject($idx, $tokens, $class);
                } else {
                    $statement[1] = match(true) {
                        Token::T_BOOLEAN()->equals($token) => $value === 'true',
                        Token::T_COLOR()->equals($token) => new Color($value),
                        Token::T_NUMBER()->equals($token) => (float)$value,
                        Token::T_STRING()->equals($token) => $value,
                        Token::T_NULL()->equals($token) => null,
                        default => throw ParseException::invalidSyntax($token, $location),
                    };
                }
            } elseif (Token::SYMBOL()->equals($token)) {
                $statement[0] = $value;
                $statement[2] = $location;
            } else {
                throw ParseException::invalidSyntax($token, $location);
            }
        }

        if (\is_null($statement[0]) || \is_null($statement[2])) {
            throw ParseException::badOperation(':', '');
        }

        return $statement;
    }

    /**
     * @param int $idx
     * @param list<array{0:\GCSS\Syntax\Lexer\Token,1:string,2:string}> $tokens The output from the Lexer.
     * @return array<string,bool|Color|Resource|float|string|null>
     */
    private function buildArray(int &$idx, array $tokens): array
    {
        $ret = [];
        while (! Token::BLOCK_END()->equals($tokens[$idx][0])) {
            [$key, $value] = $this->readStatement($idx, $tokens);
            $ret[$key] = $value;
            $idx++;
        }

        return $ret;
    }
}
