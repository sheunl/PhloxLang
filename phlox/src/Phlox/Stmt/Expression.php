<?php 

namespace Phlox\Stmt;

use Phlox\Expr\Expr;

/**
 * Represents an expression statement in the AST
 * Example: print "hello";
 */
class Expression extends Stmt
{
    /**
     * Create a new expression statement
     * @param Expr $expression The expression to execute
     */
    public function __construct(public Expr $expression){}

    /**
     * Accept a visitor to process this statement
     * @param Visitor $visitor The visitor to accept
     * @return mixed The result of visiting this node
     */
    public function accept(Visitor $visitor){
        return $visitor->visitExpressionStmt($this);
    }
}