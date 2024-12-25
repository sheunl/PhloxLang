<?php

namespace Phlox;

use Phlox\DS\Map;

/**
 * Represents an instance of a Lox class
 * Handles instance fields and method access
 */
class LoxInstance
{
    /**
     * Map of instance fields and their values
     */
    private Map $fields;

    /**
     * Get the fields map, initializing if needed
     * @return Map The instance fields map
     */
    private function getFields():Map
    {
        if (! isset($this->fields)){
            $this->fields = new Map();
        }
        return $this->fields;
    }

    /**
     * Create a new instance of a Lox class
     * @param LoxClass $klass The class to instantiate
     */
    public function __construct(private LoxClass $klass){}

    /**
     * Get a property or method from this instance
     * @param Token $name The property/method name token
     * @return mixed The property value or bound method
     * @throws RuntimeError if property/method not found
     */
    function get(Token $name)
    {
        if ($this->getFields()->hasKey($name->lexeme)){
            return $this->getFields()->get($name->lexeme);
        }

        $method = $this->klass->findMethod($name->lexeme);
        if($method != null) return $method->bind($this);

        throw new RuntimeError($name, "Undefined property '". $name->lexeme. "'.");
    }

    /**
     * Set a property value on this instance
     * @param Token $name The property name token
     * @param mixed $value The value to set
     */
    function set(Token $name, $value)
    {
        $this->getFields()->put($name->lexeme, $value);
    }

    /**
     * Get string representation of this instance
     * @return string The instance description
     */
    public function __toString()
    {
        return $this->klass->name . " instance";
    }
}