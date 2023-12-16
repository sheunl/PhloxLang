<?php

namespace Phlox\Expr;

use Phlox\Token;

class Set extends Expr
{
    public function __construct(public Expr $object, public Token $name, public Expr $value){}

    public function accept(Visitor $visitor){
        return $visitor->visitSetExpr($this);
    }
}
