<?php

namespace Phlox\Expr;

use Phlox\Token;

class Call extends Expr
{
    public function __construct(public Expr $callee, public Token $paren, public array $arguments){}

    public function accept(Visitor $visitor){
        return $visitor->visitCallExpr($this);
    }
}
