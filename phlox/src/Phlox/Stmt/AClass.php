<?php
 
 namespace Phlox\Stmt;

use Phlox\Expr\Expr;
use Phlox\Expr\Variable;
use Phlox\Token;

class AClass extends Stmt
{

    public function __construct(public Token $name, public ?Variable $superclass,  public ?array $methods = null){}

    public function accept(Visitor $visitor)
    {
        return $visitor->visitClassStmt($this);
    }

}


    // Class(Token name,
    //       Expr.Variable superclass,
    //       List<Stmt.Function> methods) {
    //   this.name = name;
    //   this.superclass = superclass;
    //   this.methods = methods;
    // }

    // @Override
    // <R> R accept(Visitor<R> visitor) {
    //   return visitor.visitClassStmt(this);
    // }

    // final Token name;
    // final Expr.Variable superclass;
    // final List<Stmt.Function> methods;
