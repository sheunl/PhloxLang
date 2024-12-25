<?php
namespace Phlox;

/**
 * Interface for callable objects in the Lox language
 * Implemented by both functions and classes
 */
interface LoxCallable {
    /**
     * Get the number of arguments this callable expects
     * @return int The arity (parameter count)
     */
    function arity():int;

    /**
     * Execute this callable with the given arguments
     * @param Interpreter $interpreter The current interpreter instance
     * @param array $arguments The arguments to pass to the callable
     * @return mixed The result of the call
     */
    function call(Interpreter $interpreter, array $arguments);
}