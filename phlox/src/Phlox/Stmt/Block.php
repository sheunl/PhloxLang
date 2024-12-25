<?php 

namespace Phlox\Stmt;

/**
 * Represents a block statement in the AST
 * Example: { statement1; statement2; }
 */
class Block extends Stmt
{
    /**
     * Create a new block statement
     * @param array $statements Array of statements in the block
     */
    public function __construct(public ?array $statements){}

    /**
     * Accept a visitor to process this statement
     * @param Visitor $visitor The visitor to accept
     * @return mixed The result of visiting this node
     */
    public function accept(Visitor $visitor)
    {
        return $visitor->visitBlockStmt($this);
    }
}