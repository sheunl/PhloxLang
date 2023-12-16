<?php

namespace Phlox\Expr;

use Phlox\Token;

class Grouping extends Expr
{
    public function __construct(public Expr $expression){}

    public function accept(Visitor $visitor){
        return $visitor->visitGroupingExpr($this);
    }
}
