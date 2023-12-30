<?php
//   static class Function extends Stmt {
//     Function(Token name, List<Token> params, List<Stmt> body) {
//       this.name = name;
//       this.params = params;
//       this.body = body;
//     }

//     @Override
//     <R> R accept(Visitor<R> visitor) {
//       return visitor.visitFunctionStmt(this);
//     }

//     final Token name;
//     final List<Token> params;
//     final List<Stmt> body;
//   }
namespace Phlox\Stmt;

use Phlox\Expr\Expr;
use Phlox\Token;

class Function_ extends Stmt
{
    public function __construct(public Token $name, public ?array $params , public ?array $body){ }

    public function accept(Visitor $visitor){
        return $visitor->visitFunctionStmt($this);
    }
}