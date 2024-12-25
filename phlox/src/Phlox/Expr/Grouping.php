<?php

namespace Phlox\Expr;

use Phlox\Token;

/**
 * Represents a grouping expression in the AST
 * Example: (1 + 2)
 */
class Grouping extends Expr
{
    /**
     * Create a new grouping expression
     * @param Expr $expression The expression being grouped
     */
    public function __construct(public Expr $expression){}

    /**
     * Accept a visitor to process this expression
     * @param Visitor $visitor The visitor to accept
     * @return mixed The result of visiting this node
     */
    public function accept(Visitor $visitor){
        return $visitor->visitGroupingExpr($this);
    }
}
