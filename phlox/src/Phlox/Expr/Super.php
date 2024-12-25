<?php

namespace Phlox\Expr;

use Phlox\Token;

/**
 * Represents a super expression in the AST for accessing superclass methods
 * Example: super.method()
 */
class Super extends Expr
{
    /**
     * Create a new super expression
     * @param Token $keyword The 'super' keyword token
     * @param Token $method The method name token being accessed
     */
    public function __construct(public Token $keyword, public Token $method){}

    /**
     * Accept a visitor to process this expression
     * @param Visitor $visitor The visitor to accept
     * @return mixed The result of visiting this node
     */
    public function accept(Visitor $visitor){
        return $visitor->visitSuperExpr($this);
    }
}