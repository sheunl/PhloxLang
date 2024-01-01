<?php

namespace Phlox\DS;

use RuntimeException;

class Stack 
{
    protected array $stack;
    protected int $limit;

    public function __construct($limit = null, $initial = [])
    {
        $this->stack = $initial;
        $this->limit = $limit;
    }

    public function push($item) 
    {
        if ($this->limit === null || count($this->stack) < $this->limit) {
            array_unshift($this->stack, $item);
        } else {
            throw new RunTimeException('Stack is full!');
        }
    }

    public function pop()
    {
        if(empty($this->stack)){
            throw new RuntimeException("Stack is empty!");
        } else {
            return array_shift($this->stack);
        }
    }

    public function top() {
        return current($this->stack);
    }

    public function isEmpty()
    {
        return count($this->stack) === 0;
    }
}