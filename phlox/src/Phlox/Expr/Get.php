<?php

namespace Phlox\Expr;

use Phlox\Token;

/**
 * Represents a property access expression in the AST
 * Example: object.property
 */
class Get extends Expr
{
    /**
     * Create a new property access expression
     * @param Expr $object The object being accessed
     * @param Token $name The property name token
     */
    public function __construct(public Expr $object, public Token $name){}

    /**
     * Accept a visitor to process this expression
     * @param Visitor $visitor The visitor to accept
     * @return mixed The result of visiting this node
     */
    public function accept(Visitor $visitor){
        return $visitor->visitGetExpr($this);
    }
}