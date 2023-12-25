<?php 

namespace Phlox\Stmt;

class Block extends Stmt
{

    public function __construct(public ?array $statements){}

    public function accept(Visitor $visitor)
    {
        return $visitor->visitBlockStmt($this);
    }
}