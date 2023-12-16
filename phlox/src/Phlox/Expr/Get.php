<?php

namespace Phlox\Expr;

use Phlox\Token;

class Get extends Expr
{
    public function __construct(public Expr $object, public Token $name){}

    public function accept(Visitor $visitor){
        return $visitor->visitGetExpr($this);
    }
}