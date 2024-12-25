<?php

namespace Phlox\DS;

use RuntimeException;

/**
 * Stack implements a Last-In-First-Out (LIFO) data structure
 * with optional size limit. Elements are added to and removed from
 * the beginning of the stack.
 */
class Stack 
{
    protected array $stack;
    protected int $limit;

    /**
     * Initializes a new Stack instance
     * @param int|null $limit Maximum number of items the stack can hold (null for unlimited)
     * @param array $initial Initial array of items to populate the stack
     */
    public function __construct($limit = null, $initial = [])
    {
        $this->stack = $initial;
        $this->limit = $limit;
    }

    /**
     * Pushes an item onto the top of the stack
     * @param mixed $item The item to push onto the stack
     * @throws RuntimeException if the stack is full
     * @return void
     */
    public function push($item) 
    {
        if ($this->limit === null || count($this->stack) < $this->limit) {
            array_unshift($this->stack, $item);
        } else {
            throw new RunTimeException('Stack is full!');
        }
    }

    /**
     * Removes and returns the item from the top of the stack
     * @throws RuntimeException if the stack is empty
     * @return mixed The item from the top of the stack
     */
    public function pop()
    {
        if(empty($this->stack)){
            throw new RuntimeException("Stack is empty!");
        } else {
            return array_shift($this->stack);
        }
    }

    /**
     * Returns the item at the top of the stack without removing it
     * @return mixed|false The top item of the stack, or false if stack is empty
     */
    public function top() {
        return current($this->stack);
    }

    /**
     * Checks if the stack is empty
     * @return bool True if the stack is empty, false otherwise
     */
    public function isEmpty()
    {
        return count($this->stack) === 0;
    }
}