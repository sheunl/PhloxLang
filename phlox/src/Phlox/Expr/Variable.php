<?php

namespace Phlox\Expr;

use Phlox\Token;

/**
 * Represents a variable reference in the AST
 * Example: someVariable
 */
class Variable extends Expr
{
    /**
     * Create a new variable expression
     * @param Token $name The variable name token
     */
    public function __construct(public Token $name){}

    /**
     * Accept a visitor to process this expression
     * @param Visitor $visitor The visitor to accept
     * @return mixed The result of visiting this node
     */
    public function accept(Visitor $visitor){
        return $visitor->visitVariableExpr($this);
    }
}