<?php
namespace  Phlox\Stmt;

use Phlox\Expr\Expr;
use Phlox\Token;

/**
 * Represents a return statement in the AST
 * Example: return value;
 */
class ReturnR extends Stmt
{
    /**
     * Create a new return statement
     * @param Token $keyword The 'return' keyword token
     * @param Expr|null $value Optional value to return
     */
    public function __construct(public Token $keyword, public ?Expr $value){}

    /**
     * Accept a visitor to process this statement
     * @param Visitor $visitor The visitor to accept
     * @return mixed The result of visiting this node
     */
    public function accept(Visitor $visitor){
        return $visitor->visitReturnStmt($this);
    }
}
