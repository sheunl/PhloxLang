<?php
namespace  Phlox\Stmt;

use Phlox\Expr\Expr;
use Phlox\Token;

class ReturnR extends Stmt
{
    public function __construct(public Token $keyword, public Expr $value){}

    public function accept(Visitor $visitor){
        return $visitor->visitReturnStmt($this);
    }
}

// static class Return extends Stmt {
//     Return(Token keyword, Expr value) {
//       this.keyword = keyword;
//       this.value = value;
//     }

//     @Override
//     <R> R accept(Visitor<R> visitor) {
//       return visitor.visitReturnStmt(this);
//     }

//     final Token keyword;
//     final Expr value;
//   }