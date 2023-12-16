<?php

namespace Phlox\Stmt;

interface Visitor{
    public function visitExpressionStmt(Expression $stmt);
    public function visitPrintStmt(Printr $stmt);
    public function visitVarStmt(Var_ $stmt);
}