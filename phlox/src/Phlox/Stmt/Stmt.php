<?php
namespace Phlox\Stmt;

use Phlox\Interpreter;
use Phlox\Token;
use Phlox\TokenType;
use Phlox\Phlox;

abstract class Stmt{
    abstract public function accept(Visitor $visitor);
}