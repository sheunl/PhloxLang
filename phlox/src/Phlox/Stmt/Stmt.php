<?php
namespace Phlox\Stmt;

use Phlox\Interpreter;
use Phlox\Token;
use Phlox\TokenType;
use Phlox\Phlox;

/**
 * Base abstract class for all statements in the AST
 * Implements the Visitor pattern for traversing the AST
 */
abstract class Stmt {
    /**
     * Accept a visitor to process this statement
     * @param Visitor $visitor The visitor to accept
     * @return mixed The result of visiting this node
     */
    abstract public function accept(Visitor $visitor);
}