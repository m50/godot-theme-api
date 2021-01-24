<?php

declare(strict_types=1);

namespace GCSS\Syntax\Parser;

use Exception;
use GCSS\Syntax\Lexer\Token;
use ReflectionClass;

class ParseException extends Exception
{
    private function __construct(string $message)
    {
        $this->message = $message;
        $this->code = 400;
    }

    public static function unknownNode(string $nodeName, string $location): ParseException
    {
        return new ParseException("Unknown Node '{$nodeName}' at {$location}");
    }

    public static function unknownResource(string $resourceName, string $location): ParseException
    {
        return new ParseException("Unknown Resource '{$resourceName}' at {$location}");
    }

    /**
     * @param class-string $nodeName
     * @param string $property
     * @param string $location
     * @return ParseException
     */
    public static function propertyDoesntExist(string $nodeName, string $property, string $location): ParseException
    {
        $reflect = new ReflectionClass($nodeName);
        $shortName = $reflect->getShortName();

        return new ParseException("Property {$property} does not exist on {$shortName} at {$location}.");
    }

    public static function invalidSyntax(Token $token, string $location): ParseException
    {
        return new ParseException("Parse error: Invalid token {$token->getKey()} at {$location}.");
    }

    public static function badOperation(string $operator, string $location): ParseException
    {
        return new ParseException("Parse error: Invalid operator '{$operator}' at {$location}.");
    }
}
