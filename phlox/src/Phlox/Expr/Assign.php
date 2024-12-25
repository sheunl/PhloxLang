<?php

namespace Phlox\Expr;

use Phlox\Token;

/**
 * Represents an assignment expression in the AST
 * Example: x = 5
 */
class Assign extends Expr 
{
    /**
     * Create a new assignment expression
     * @param Token $name The variable name being assigned to
     * @param Expr $value The value being assigned
     */
    public function __construct(public Token $name, public Expr $value){}
    
    /**
     * Accept a visitor to process this expression
     * @param Visitor $visitor The visitor to accept
     * @return mixed The result of visiting this node
     */
    public function accept(Visitor $visitor){
        return $visitor->visitAssignExpr($this);
    }
}