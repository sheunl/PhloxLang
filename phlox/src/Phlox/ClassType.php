<?php

namespace Phlox;

/**
 * Defines constants for different class types in the Lox language
 */
class ClassType
{
    /**
     * Represents no class context
     */
    const NONE = 'NONE';

    /**
     * Represents a regular class context
     */
    const ACLASS = 'CLASS';

    /**
     * Represents a subclass context
     */
    const SUBCLASS = 'SUBCLASS';
}