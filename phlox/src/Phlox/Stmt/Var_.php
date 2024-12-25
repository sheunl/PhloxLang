<?php 

namespace Phlox\Stmt;

use Phlox\Expr\Expr;
use Phlox\Token;

/**
 * Represents a variable declaration statement in the AST
 * Example: var x = 10;
 */
class Var_ extends Stmt
{
    /**
     * Create a new variable declaration
     * @param Token $name The variable name token
     * @param Expr|null $intializer Optional initializer expression
     */
    public function __construct(public Token $name, public ?Expr $intializer){ }

    /**
     * Accept a visitor to process this statement
     * @param Visitor $visitor The visitor to accept
     * @return mixed The result of visiting this node
     */
    public function accept(Visitor $visitor){
        return $visitor->visitVarStmt($this);
    }
}