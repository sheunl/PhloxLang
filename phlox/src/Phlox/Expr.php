<?php

namespace Phlox;

use Phlox\Token;

interface Visitor{
    public function visitAssignExpr(Assign $expr);
    public function visitBinaryExpr(Binary $expr);
    public function visitCallExpr(Call $expr);
    public function visitGetExpr(Get $expr);
    public function visitGroupingExpr(Grouping $expr);
    public function visitLiteralExpr(Literal $expr);
    public function visitLogicalExpr(Logical $expr);
    public function visitSetExpr(Set $expr);
    public function visitSuperExpr(Super $expr);
    public function visitThisExpr(This $expr);
    public function visitUnaryExpr(Unary $expr);
    public function visitVariableExpr(Variable $expr);
}

class Assign extends Expr 
{

   public function __construct( public Token $name, public Expr $value){}
    
   public function accept(Visitor $visitor){
    return $visitor->visitAssignExpr($this);
   }
}

class Binary extends Expr 
{

   public function __construct( public Expr $left, public Token $name, public Expr $right){}
    
   public function accept(Visitor $visitor){
    return $visitor->visitBinaryExpr($this);
   }
}

class Call extends Expr
{
    public function __construct(public Expr $callee, public Token $name, array $argument){}

    public function accept(Visitor $visitor){
        return $visitor->visitCallExpr($this);
    }
}

class Get extends Expr
{
    public function __construct(public Expr $object, public Token $name){}

    public function accept(Visitor $visitor){
        return $visitor->visitGetExpr($this);
    }
}

class Grouping extends Expr
{
    public function __construct(public Expr $expression){}

    public function accept(Visitor $visitor){
        return $visitor->visitGroupingExpr($this);
    }
}

class Literal extends Expr
{
    public function __construct(object $object){}

    public function accept(Visitor $visitor){
        return $visitor->visitLiteralExpr($this);
    }
}

class Logical extends Expr
{
    public function __construct(public Expr $left, public Token $operator, public Expr $right){}

    public function accept(Visitor $visitor){
        return $visitor->visitLogicalExpr($this);
    }
}

class Set extends Expr
{
    public function __construct(public Expr $object, public Token $name, public Expr $value){}

    public function accept(Visitor $visitor){
        return $visitor->visitSetExpr($this);
    }
}

class Super extends Expr
{
    public function __construct(public Token $keyword, public Token $method){}

    public function accept(Visitor $visitor){
        return $visitor->visitSuperExpr($this);
    }
}

class This extends Expr
{
    public function __construct(public Token $keyword){}

    public function accept(Visitor $visitor){
        return $visitor->visitThisExpr($this);
    }
}

class Unary extends Expr
{
    public function __construct(public Token $operator, public Expr $right){}

    public function accept(Visitor $visitor){
        return $visitor->visitUnaryExpr($this);
    }
}

class Variable extends Expr
{
    public function __construct(public Token $name){}

    public function accept(Visitor $visitor){
        return $visitor->visitVariableExpr($this);
    }
}



abstract class Expr {
    abstract public function accept(Visitor $visitor);
}