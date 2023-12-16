<?php

namespace Phlox\Expr;

use Phlox\Token;

class This extends Expr
{
    public function __construct(public Token $keyword){}

    public function accept(Visitor $visitor){
        return $visitor->visitThisExpr($this);
    }
}
