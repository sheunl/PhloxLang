<?php

namespace Phlox\Expr;

abstract class Expr {
    abstract public function accept(Visitor $visitor);
}