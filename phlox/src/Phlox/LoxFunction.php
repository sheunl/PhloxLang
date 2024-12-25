<?php
namespace Phlox;

use Phlox\Stmt\Function_;

/**
 * Represents a function or method in the Lox language
 * Handles function calls, closures, and method binding
 */
class LoxFunction implements LoxCallable
{
    /**
     * Create a new Lox function
     * @param Function_ $declaration The AST node for the function declaration
     * @param Environment $closure The environment where the function was defined
     * @param bool $isInitializer Whether this is a class initializer method
     */
    public function __construct(private Function_ $declaration, private Environment $closure, private bool $isInitializer){}

    /**
     * Bind this function as a method to an instance
     * Creates a new environment with 'this' bound to the instance
     * @param LoxInstance $instance The instance to bind to
     * @return LoxFunction A new function with 'this' bound
     */
    function bind(LoxInstance $instance){
        $environment = new Environment($this->closure);
        $environment->define("this", $instance);
        return new LoxFunction($this->declaration, $environment, $this->isInitializer);
    }

    /**
     * Execute the function with given arguments
     * Implements LoxCallable interface
     * @param Interpreter $interpreter The current interpreter
     * @param array $arguments The arguments to pass to the function
     * @return mixed The function's return value
     */
    public function call(Interpreter $interpreter, array $arguments)
    {
        $environment = new Environment($this->closure);

        for ($i = 0; $i < count($this->declaration->params); $i++){
            $environment->define($this->declaration->params[$i]->lexeme, $arguments[$i]);
        }

        try {
            $interpreter->executeBlock($this->declaration->body, $environment);
        } catch (Return_ $returnValue) {
            if ($this->isInitializer) return $this->closure->getAt(0, "this");
            return $returnValue->value;
        }

        if ($this->isInitializer) return $this->closure->getAt(0, "this");
        return null;
    }

    /**
     * Get the number of parameters this function accepts
     * Implements LoxCallable interface
     * @return int The number of parameters
     */
    public function arity(): int
    {
        return count($this->declaration->params);
    }

    /**
     * Get string representation of this function
     * @return string The function description
     */
    public function __toString()
    {
        return "<fn ".$this->declaration->name->lexeme.">";
    }
}