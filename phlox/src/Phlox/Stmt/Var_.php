<?php 

namespace Phlox\Stmt;

use Phlox\Expr\Expr;
use Phlox\Token;

class Var_ extends Stmt
{

    // Var(Token name, Expr initializer) {
    //     this.name = name;
    //     this.initializer = initializer;
    //   }
  
    //   @Override
    //   <R> R accept(Visitor<R> visitor) {
    //     return visitor.visitVarStmt(this);
    //   }
  
    //   final Token name;
    //   final Expr initializer;
    public function __construct(public Token $name, public ?Expr $intializer){ }

    public function accept(Visitor $visitor){
        return $visitor->visitVarStmt($this);
    }
}