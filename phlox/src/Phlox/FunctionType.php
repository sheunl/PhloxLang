<?php

namespace Phlox;

/**
 * Defines constants for different function types in the Lox language
 */
class FunctionType
{
    /**
     * Represents no function context
     */
    const NONE = 'NONE';

    /**
     * Represents a regular function context
     */
    const FUNCTION = 'FUNCTION';

    /**
     * Represents a method context
     */
    const METHOD = 'METHOD';

    /**
     * Represents a class initializer method context
     */
    const INITIALIZER = 'INITIALIZER';
}