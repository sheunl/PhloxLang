<?php

namespace Phlox\Stmt;

use Phlox\Expr\Expr;
use Phlox\Token;

/**
 * Represents a function declaration in the AST
 * Example: fun add(a, b) { return a + b; }
 */
class Function_ extends Stmt
{
     /**
     * Create a new function declaration
     * @param Token $name The function name token
     * @param array $params Array of parameter tokens
     * @param array $body Array of statements in function body
     */
    public function __construct(public Token $name, public ?array $params , public ?array $body){ }

    /**
     * Accept a visitor to process this statement
     * @param Visitor $visitor The visitor to accept
     * @return mixed The result of visiting this node
     */
    public function accept(Visitor $visitor){
        return $visitor->visitFunctionStmt($this);
    }
}