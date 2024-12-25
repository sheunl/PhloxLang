<?php

namespace Phlox\Expr;

use Phlox\Token;

/**
 * Represents a logical operation in the AST
 * Examples: a and b, x or y
 */
class Logical extends Expr
{
    /**
     * Create a new logical expression
     * @param Expr $left The left operand
     * @param Token $operator The logical operator token (and/or)
     * @param Expr $right The right operand
     */
    public function __construct(public Expr $left, public Token $operator, public Expr $right){}

    /**
     * Accept a visitor to process this expression
     * @param Visitor $visitor The visitor to accept
     * @return mixed The result of visiting this node
     */
    public function accept(Visitor $visitor){
        return $visitor->visitLogicalExpr($this);
    }
}