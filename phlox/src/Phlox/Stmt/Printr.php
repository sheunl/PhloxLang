<?php 

namespace Phlox\Stmt;

use Phlox\Expr\Expr;

class Printr extends Stmt
{

    public function __construct(public Expr $expression){}

    public function accept(Visitor $visitor)
    {
        return $visitor->visitPrintStmt($this);
    }
}