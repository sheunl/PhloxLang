<?php

namespace Phlox\Expr;

interface Visitor{
    public function visitAssignExpr(Assign $expr);
    public function visitBinaryExpr(Binary $expr);
    public function visitCallExpr(Call $expr);
    // public function visitGetExpr(Get $expr);
    public function visitGroupingExpr(Grouping $expr);
    public function visitLiteralExpr(Literal $expr);
    public function visitLogicalExpr(Logical $expr);
    // public function visitSetExpr(Set $expr);
    // public function visitSuperExpr(Super $expr);
    // public function visitThisExpr(This $expr);
    public function visitUnaryExpr(Unary $expr);
    public function visitVariableExpr(Variable $expr);
}