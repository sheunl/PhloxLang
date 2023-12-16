<?php

namespace Phlox\Expr;

use Phlox\Token;

class Variable extends Expr
{
    public function __construct(public Token $name){}

    public function accept(Visitor $visitor){
        return $visitor->visitVariableExpr($this);
    }
}