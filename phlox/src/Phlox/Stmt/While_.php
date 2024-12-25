<?php

namespace Phlox\Stmt;

use Phlox\Expr\Expr;

/**
 * Represents a while loop statement in the AST
 * Example: while (condition) { body }
 */
class While_ extends Stmt
{
    /**
     * Create a new while statement
     * @param Expr $condition The loop condition expression
     * @param Stmt $body The loop body statement
     */
    public function __construct(public Expr $condition, public Stmt $body){}

    /**
     * Accept a visitor to process this statement
     * @param Visitor $visitor The visitor to accept
     * @return mixed The result of visiting this node
     */
    public function accept(Visitor $visitor){
        return $visitor->visitWhileStmt($this);
    }
}
