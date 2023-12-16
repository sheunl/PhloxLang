<?php

namespace Phlox\Expr;

use Phlox\Token;

class Unary extends Expr
{
    public function __construct(public Token $operator, public Expr $right){}

    public function accept(Visitor $visitor){
        return $visitor->visitUnaryExpr($this);
    }
}