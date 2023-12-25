<?php


namespace Phlox\Stmt;

use Phlox\Expr\Expr;

class If_ extends Stmt
{

    public function __construct(public Expr $condition, public Stmt $thenBranch, public ?Stmt $elseBranch){}

    public function accept(Visitor $visitor)
    {
      return $visitor->visitIfStmt($this);
    }
}

/* static class If extends Stmt {
    If(Expr condition, Stmt thenBranch, Stmt elseBranch) {
      this.condition = condition;
      this.thenBranch = thenBranch;
      this.elseBranch = elseBranch;
    }

    @Override
    <R> R accept(Visitor<R> visitor) {
      return visitor.visitIfStmt(this);
    }

    final Expr condition;
    final Stmt thenBranch;
    final Stmt elseBranch;
  } */

