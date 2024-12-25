<?php

namespace Phlox\Expr;

/**
 * Interface for implementing the Visitor pattern for expressions
 * Each method corresponds to a type of expression in the AST
 */
interface Visitor{
    /**
     * Visit an assignment expression
     * @param Assign $expr The assignment expression to visit
     * @return mixed The result of visiting the expression
     */
    public function visitAssignExpr(Assign $expr);

    /**
     * Visit a binary expression
     * @param Binary $expr The binary expression to visit
     * @return mixed The result of visiting the expression
     */
    public function visitBinaryExpr(Binary $expr);

    /**
     * Visit a function call expression
     * @param Call $expr The call expression to visit
     * @return mixed The result of visiting the expression
     */
    public function visitCallExpr(Call $expr);

    /**
     * Visit a property access expression
     * @param Get $expr The property access expression to visit
     * @return mixed The result of visiting the expression
     */
    public function visitGetExpr(Get $expr);

    /**
     * Visit a grouping expression
     * @param Grouping $expr The grouping expression to visit
     * @return mixed The result of visiting the expression
     */
    public function visitGroupingExpr(Grouping $expr);

    /**
     * Visit a literal value expression
     * @param Literal $expr The literal expression to visit
     * @return mixed The result of visiting the expression
     */
    public function visitLiteralExpr(Literal $expr);

    /**
     * Visit a logical operation expression
     * @param Logical $expr The logical expression to visit
     * @return mixed The result of visiting the expression
     */
    public function visitLogicalExpr(Logical $expr);

    /**
     * Visit a property assignment expression
     * @param Set $expr The property assignment expression to visit
     * @return mixed The result of visiting the expression
     */
    public function visitSetExpr(Set $expr);

    /**
     * Visit a super expression
     * @param Super $expr The super expression to visit
     * @return mixed The result of visiting the expression
     */
    public function visitSuperExpr(Super $expr);

    /**
     * Visit a this expression
     * @param This $expr The this expression to visit
     * @return mixed The result of visiting the expression
     */
    public function visitThisExpr(This $expr);

    /**
     * Visit a unary expression
     * @param Unary $expr The unary expression to visit
     * @return mixed The result of visiting the expression
     */
    public function visitUnaryExpr(Unary $expr);

    /**
     * Visit a variable reference expression
     * @param Variable $expr The variable expression to visit
     * @return mixed The result of visiting the expression
     */
    public function visitVariableExpr(Variable $expr);
}