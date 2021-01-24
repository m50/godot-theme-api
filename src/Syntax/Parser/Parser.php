<?php

declare(strict_types=1);

namespace GCSS\Syntax\Parser;

use GCSS\Syntax\Lexer\Token;
use Generator;
use Underscore\Parse;
use Underscore\Types\Strings;

class Parser
{
    /**
     * Process the tokens from the Lexer and turn it into an object tree.
     *
     * @param list<array{0:\GCSS\Syntax\Lexer\Token,1:string}> $tokens The output from the Lexer.
     * @return array<string,\GCSS\Godot\Nodes\Control|\GCSS\Godot\Resources\DynamicFont>
     */
    public function process(array $tokens): array
    {
        $parsedObjects = [];

        $state = 'root';

        $idx = 0;
        while (isset($tokens[$idx])) {
            $token = $tokens[$idx];
            if ($state === 'root') {
                if ($token[0] === Token::SYMBOL()) {
                    if ($token[1] === 'default-font') {

                    } else {
                        $class = '\\GCSS\\Godot\\Nodes\\' . Strings::toCamelCase($token[1]);
                        if (! class_exists($class)) {
                            throw ParserException::unknownNode($token[1]);
                        }
                    }
                    $state = 'declaration';
                }
            }
            $idx++;
        }

        return $parsedObjects;
    }
}
