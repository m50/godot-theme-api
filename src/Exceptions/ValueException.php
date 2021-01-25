<?php

declare(strict_types=1);

namespace GCSS\Exceptions;

use Exception;
use ReflectionClass;

class ValueException extends Exception
{
    /**
     * @param class-string $class
     * @param string $property
     */
    public static function missing(string $class, string $property): ValueException
    {
        $reflect = new ReflectionClass($class);
        $shortName = $reflect->getShortName();

        return ValueException::missing("{$shortName}/{$property} doesn't exist.", 400);
    }

    /**
     * @param class-string $class
     * @param string $property
     */
    public static function untyped(string $class, string $property): ValueException
    {
        $reflect = new ReflectionClass($class);
        $shortName = $reflect->getShortName();

        return ValueException::missing("{$shortName}/{$property} is not properly typed.", 400);
    }
}
