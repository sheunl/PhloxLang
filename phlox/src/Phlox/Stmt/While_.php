<?php


namespace Phlox\Stmt;

use Phlox\Expr\Expr;

class While_ extends Stmt
{

    public function __construct(public Expr $condition, public Stmt $body){}

    public function accept(Visitor $visitor)
    {
      return $visitor->visitWhileStmt($this);
    }
}

// static class While extends Stmt {
//     While(Expr condition, Stmt body) {
//       this.condition = condition;
//       this.body = body;
//     }

//     @Override
//     <R> R accept(Visitor<R> visitor) {
//       return visitor.visitWhileStmt(this);
//     }

//     final Expr condition;
//     final Stmt body;
//   }