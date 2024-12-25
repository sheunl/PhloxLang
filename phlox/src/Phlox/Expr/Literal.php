<?php

namespace Phlox\Expr;

use Phlox\Token;

/**
 * Represents a literal value in the AST
 * Examples: 123, "hello", true, nil
 */
class Literal extends Expr
{
    /**
     * Create a new literal expression
     * @param mixed $value The literal value
     */
    public function __construct(public $value){}

    /**
     * Accept a visitor to process this expression
     * @param Visitor $visitor The visitor to accept
     * @return mixed The result of visiting this node
     */
    public function accept(Visitor $visitor){
        return $visitor->visitLiteralExpr($this);
    }
}