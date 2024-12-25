<?php

namespace Phlox\Expr;

/**
 * Base abstract class for all expressions in the AST
 * Implements the Visitor pattern for traversing the AST
 */
abstract class Expr {
    /**
     * Accept a visitor to process this expression
     * @param Visitor $visitor The visitor to accept
     * @return mixed The result of visiting this node
     */
    abstract public function accept(Visitor $visitor);
}