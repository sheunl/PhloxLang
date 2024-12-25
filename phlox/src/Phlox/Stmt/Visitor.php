<?php

namespace Phlox\Stmt;

/**
 * Interface for implementing the Visitor pattern for statements
 * Each method corresponds to a type of statement in the AST
 */
interface Visitor {
    /**
     * Visit a block statement
     * @param Block $stmt The block statement to visit
     * @return mixed The result of visiting the statement
     */
    public function visitBlockStmt(Block $stmt);

    /**
     * Visit a class declaration
     * @param AClass $stmt The class declaration to visit
     * @return mixed The result of visiting the statement
     */
    public function visitClassStmt(AClass $stmt);

    /**
     * Visit an expression statement
     * @param Expression $stmt The expression statement to visit
     * @return mixed The result of visiting the statement
     */
    public function visitExpressionStmt(Expression $stmt);

    /**
     * Visit a function declaration
     * @param Function_ $stmt The function declaration to visit
     * @return mixed The result of visiting the statement
     */
    public function visitFunctionStmt(Function_ $stmt);

    /**
     * Visit an if statement
     * @param If_ $stmt The if statement to visit
     * @return mixed The result of visiting the statement
     */
    public function visitIfStmt(If_ $stmt);

    /**
     * Visit a print statement
     * @param Printr $stmt The print statement to visit
     * @return mixed The result of visiting the statement
     */
    public function visitPrintStmt(Printr $stmt);

    /**
     * Visit a return statement
     * @param ReturnR $stmt The return statement to visit
     * @return mixed The result of visiting the statement
     */
    public function visitReturnStmt(ReturnR $stmt);

    /**
     * Visit a variable declaration
     * @param Var_ $stmt The variable declaration to visit
     * @return mixed The result of visiting the statement
     */
    public function visitVarStmt(Var_ $stmt);

    /**
     * Visit a while statement
     * @param While_ $stmt The while statement to visit
     * @return mixed The result of visiting the statement
     */
    public function visitWhileStmt(While_ $stmt);
}