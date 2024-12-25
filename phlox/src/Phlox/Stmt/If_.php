<?php

namespace Phlox\Stmt;

use Phlox\Expr\Expr;

/**
 * Represents an if statement in the AST
 * Example: if (condition) { ... } else { ... }
 */
class If_ extends Stmt
{
    /**
     * Create a new if statement
     * @param Expr $condition The condition expression
     * @param Stmt $thenBranch Statement to execute if condition is true
     * @param Stmt|null $elseBranch Optional statement to execute if condition is false
     */
    public function __construct(
        public Expr $condition, 
        public Stmt $thenBranch, 
        public ?Stmt $elseBranch
    ){}

    /**
     * Accept a visitor to process this statement
     * @param Visitor $visitor The visitor to accept
     * @return mixed The result of visiting this node
     */
    public function accept(Visitor $visitor){
        return $visitor->visitIfStmt($this);
    }
}


