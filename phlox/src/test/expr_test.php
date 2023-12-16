<?php

use Phlox\Expr;
use Phlox\Visitor;


require "../Phlox/Expr.php";


class Item extends Expr
{
    function accept(Visitor $visitor)
    {
        
    }
}

$expr = new Item();
$visit = new Visitor;