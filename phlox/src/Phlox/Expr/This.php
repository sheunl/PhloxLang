<?php

namespace Phlox\Expr;

use Phlox\Token;

/**
 * Represents a this expression in the AST for accessing instance members
 * Example: this.field
 */
class This extends Expr
{
    /**
     * Create a new this expression
     * @param Token $keyword The 'this' keyword token
     */
    public function __construct(public Token $keyword){}

    /**
     * Accept a visitor to process this expression
     * @param Visitor $visitor The visitor to accept
     * @return mixed The result of visiting this node
     */
    public function accept(Visitor $visitor){
        return $visitor->visitThisExpr($this);
    }
}
