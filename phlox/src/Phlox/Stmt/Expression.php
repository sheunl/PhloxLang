<?php 

namespace Phlox\Stmt;

use Phlox\Expr\Expr;

class Expression extends Stmt
{
    public function __construct(public Expr $expression){ }

    public function accept(Visitor $visitor){
        return $visitor->visitExpressionStmt($this);
    }
}