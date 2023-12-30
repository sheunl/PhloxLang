<?php

namespace Phlox\Stmt;

interface Visitor{
    public function visitExpressionStmt(Expression $stmt);
    public function visitPrintStmt(Printr $stmt);
    public function visitVarStmt(Var_ $stmt);
    public function visitBlockStmt(Block $statement);
    public function visitIfStmt(If_ $statement);
    public function visitWhileStmt(While_ $statement);
    public function visitFunctionStmt(Function_ $stmt);
    public function visitReturnStmt(ReturnR $stmt);
}