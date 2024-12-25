<?php

namespace Phlox\Expr;

use Phlox\Token;

/**
 * Represents a property assignment expression in the AST
 * Example: object.property = value
 */
class Set extends Expr
{
    /**
     * Create a new property assignment expression
     * @param Expr $object The object whose property is being set
     * @param Token $name The property name token
     * @param Expr $value The value being assigned
     */
    public function __construct(public Expr $object, public Token $name, public Expr $value){}

    /**
     * Accept a visitor to process this expression
     * @param Visitor $visitor The visitor to accept
     * @return mixed The result of visiting this node
     */
    public function accept(Visitor $visitor){
        return $visitor->visitSetExpr($this);
    }
}
