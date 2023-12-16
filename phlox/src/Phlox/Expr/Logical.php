<?php

namespace Phlox\Expr;

use Phlox\Token;

class Logical extends Expr
{
    public function __construct(public Expr $left, public Token $operator, public Expr $right){}

    public function accept(Visitor $visitor){
        return $visitor->visitLogicalExpr($this);
    }
}