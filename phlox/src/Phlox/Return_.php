<?php

namespace Phlox;

use RuntimeException;

/**
 * Return_ class handles return statements in the interpreter
 * Extends RuntimeException to use PHP's exception handling for control flow
 * Used to propagate return values up through the call stack
 */
class Return_ extends RuntimeException
{
    /** The value being returned */
    public $value;

    /**
     * Creates a new Return_ instance
     * @param mixed $value The value to return from the function
     */
    public function __construct($value)
    {
        parent::__construct("");
        $this->value = $value;
    }
}