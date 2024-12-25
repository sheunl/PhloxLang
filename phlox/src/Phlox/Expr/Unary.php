<?php

namespace Phlox\Expr;

use Phlox\Token;

/**
 * Represents a unary expression in the AST
 * Examples: !true, -42
 */
class Unary extends Expr
{
    /**
     * Create a new unary expression
     * @param Token $operator The unary operator token
     * @param Expr $right The operand expression
     */
    public function __construct(public Token $operator, public Expr $right){}

    /**
     * Accept a visitor to process this expression
     * @param Visitor $visitor The visitor to accept
     * @return mixed The result of visiting this node
     */
    public function accept(Visitor $visitor){
        return $visitor->visitUnaryExpr($this);
    }
}