<?php

namespace Phlox\Expr;

use Phlox\Token;

/**
 * Represents a binary expression in the AST
 * Examples: a + b, x * y, foo == bar
 */
class Binary extends Expr 
{
    /**
     * Create a new binary expression
     * @param Expr $left The left operand
     * @param Token $operator The operator token
     * @param Expr $right The right operand
     */
    public function __construct(public Expr $left, public Token $operator, public Expr $right){}
    
    /**
     * Accept a visitor to process this expression
     * @param Visitor $visitor The visitor to accept
     * @return mixed The result of visiting this node
     */
    public function accept(Visitor $visitor){
        return $visitor->visitBinaryExpr($this);
    }
}