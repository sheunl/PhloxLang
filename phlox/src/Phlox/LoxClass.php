<?php

namespace Phlox;

use Phlox\DS\Map;

/**
 * Represents a class in the Lox language
 * Handles method inheritance and instantiation
 */
class LoxClass implements LoxCallable
{
    /**
     * Create a new Lox class
     * @param string $name The name of the class
     * @param LoxClass|null $superclass The superclass if any
     * @param Map $methods Map of method names to LoxFunction instances
     */
    public function __construct(public string $name, public ?LoxClass $superclass, private Map $methods){}

    /**
     * Find a method in this class or its superclass chain
     * @param string $name The name of the method to find
     * @return LoxFunction|null The method if found, null otherwise
     */
    public function findMethod(string $name)
    {
        if($this->methods->hasKey($name)){
            return $this->methods->get($name);
        }

        if($this->superclass != null){
            return $this->superclass->findMethod($name);
        }

        return null;
    }

    /**
     * Get string representation of this class
     * @return string The class name
     */
    public function __toString():string
    {
        return $this->name;
    }

    /**
     * Create a new instance of this class
     * Implements LoxCallable interface
     * @param Interpreter $interpreter The current interpreter
     * @param array $arguments Constructor arguments
     * @return LoxInstance The new instance
     */
    public function call(Interpreter $interpreter, array $arguments)
    {
        $instance = new LoxInstance($this);

        $intializer = $this->findMethod("init");
        if($intializer != null)
        {
            $intializer->bind($instance)->call($interpreter, $arguments);
        }

        return $instance;
    }

    /**
     * Get the arity (parameter count) of the constructor
     * Implements LoxCallable interface
     * @return int The number of parameters
     */
    public function arity():int
    {
        $initializer = $this->findMethod("init");
        if ($initializer == null) return 0;

        return $initializer->arity();
    }
}