<?php
// namespace Test;


require "Phlox/DS/Stack.php";

$stack = new Phlox\DS\Stack(limit:10, initial:['rap' => 2, 'RnB'=>35]);

var_dump($stack->top());

// $stack->top()['there?'] = true;

var_dump($stack->pop());
