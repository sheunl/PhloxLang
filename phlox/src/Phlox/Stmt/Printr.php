<?php 

namespace Phlox\Stmt;

use Phlox\Expr\Expr;

/**
 * Represents a print statement in the AST
 * Example: print "Hello, world!";
 */
class Printr extends Stmt
{
    /**
     * Create a new print statement
     * @param Expr $expression The expression to print
     */
    public function __construct(public Expr $expression){}

    /**
     * Accept a visitor to process this statement
     * @param Visitor $visitor The visitor to accept
     * @return mixed The result of visiting this node
     */
    public function accept(Visitor $visitor)
    {
        return $visitor->visitPrintStmt($this);
    }
}