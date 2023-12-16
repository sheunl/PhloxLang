<?php

namespace Phlox\Expr;

use Phlox\Token;

class Literal extends Expr
{
    public function __construct(public $value){}

    public function accept(Visitor $visitor){
        return $visitor->visitLiteralExpr($this);
    }
}