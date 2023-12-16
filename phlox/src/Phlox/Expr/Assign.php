<?php

namespace Phlox\Expr;

use Phlox\Token;

class Assign extends Expr 
{

   public function __construct( public Token $name, public Expr $value){}
    
   public function accept(Visitor $visitor){
    return $visitor->visitAssignExpr($this);
   }
}