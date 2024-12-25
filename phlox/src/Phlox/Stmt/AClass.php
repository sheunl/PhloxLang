<?php
 
namespace Phlox\Stmt;

use Phlox\Expr\Expr;
use Phlox\Expr\Variable;
use Phlox\Token;

/**
 * Represents a class declaration in the AST
 * Example: class MyClass { ... }
 */
class AClass extends Stmt
{
    /**
     * Create a new class declaration
     * @param Token $name The class name token
     * @param Variable|null $superclass The superclass expression if any
     * @param array $methods Array of method declarations
     */
    public function __construct(public Token $name, public ?Variable $superclass,  public ?array $methods = null){}

    /**
     * Accept a visitor to process this statement
     * @param Visitor $visitor The visitor to accept
     * @return mixed The result of visiting this node
     */
    public function accept(Visitor $visitor){
        return $visitor->visitClassStmt($this);
    }
}

