<?php

namespace Phlox\Expr;

use Phlox\Token;

/**
 * Represents a function or method call expression in the AST
 * Example: foo(1, 2, 3)
 */
class Call extends Expr
{
    /**
     * Create a new call expression
     * @param Expr $callee The function being called
     * @param Token $paren The closing parenthesis token (for error reporting)
     * @param array $arguments The arguments to the function
     */
    public function __construct(public Expr $callee, public Token $paren, public array $arguments){}

    /**
     * Accept a visitor to process this expression
     * @param Visitor $visitor The visitor to accept
     * @return mixed The result of visiting this node
     */
    public function accept(Visitor $visitor){
        return $visitor->visitCallExpr($this);
    }
}
