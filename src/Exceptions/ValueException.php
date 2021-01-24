<?php

declare(strict_types=1);

namespace GCSS\Exceptions;

use Exception;
use ReflectionClass;

class ValueException extends Exception
{
    /**
     * @param class-string $class
     * @param string $key
     */
    public function __construct(string $class, string $key)
    {
        $reflect = new ReflectionClass($class);
        $shortName = $reflect->getShortName();
        $this->message = "{$shortName}/{$key} doesn't exist.";
    }
}
