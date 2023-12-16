<?php

namespace Phlox\Expr;

use Phlox\Token;

class Super extends Expr
{
    public function __construct(public Token $keyword, public Token $method){}

    public function accept(Visitor $visitor){
        return $visitor->visitSuperExpr($this);
    }
}