<?php
namespace Phlox;

use Exception;

class RuntimeError extends Exception
{

    public Token $token;

    public function __construct(Token $token, string $message)
    {
        parent::__construct($message);
        $this->$token = $token;
    }

}
