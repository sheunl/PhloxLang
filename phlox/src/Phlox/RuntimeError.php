<?php
namespace Phlox;

use Exception;

/**
 * RuntimeError represents runtime errors in the interpreter
 * Extends PHP's Exception class to provide error handling for Phlox language
 */
class RuntimeError extends Exception
{
    /** The token where the runtime error occurred */
    public Token $token;

    /**
     * Creates a new RuntimeError instance
     * @param Token $token The token where the error occurred
     * @param string $message Description of the runtime error
     */
    public function __construct(Token $token, string $message)
    {
        parent::__construct($message);
        $this->$token = $token;
    }
}
