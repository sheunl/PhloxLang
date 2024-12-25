<?php
namespace Phlox;

/**
 * Represents a scope environment for variable bindings
 * Handles variable definition, assignment and lookup with lexical scoping
 */
class Environment 
{
    /**
     * Map of variable names to their values in this scope
     * @var array
     */
    private $values = [];

    /**
     * Create a new environment
     * @param Environment|null $enclosing The enclosing (parent) environment
     */
    public function __construct(public $enclosing = null){}

    /**
     * Define a new variable in the current environment
     * @param string $name The variable name
     * @param mixed $value The variable value
     */
    public function define(string $name, $value)
    {
        $this->values[$name] = $value;
    }

    /**
     * Get an environment at a given distance in the scope chain
     * @param int $distance Number of scopes to traverse
     * @return Environment The environment at the given distance
     */
    function ancestor(int $distance):Environment
    {
        $environment = $this;

        for ($i = 0; $i < $distance; $i++)
        {
            $environment = $environment->enclosing;
        }

        return $environment;
    }

    /**
     * Assign a value to a variable at a given scope distance
     * @param int $distance Number of scopes to traverse
     * @param Token $name The variable name token
     * @param mixed $value The value to assign
     */
    function assignAt(int $distance, Token $name, $value)
    {
        $this->ancestor($distance)->values[$name->lexeme] = $value;
    }

    /**
     * Get a variable's value at a given scope distance
     * @param int $distance Number of scopes to traverse
     * @param string $name The variable name
     * @return mixed The variable's value
     */
    function getAt(int $distance, string $name)
    {
        return $this->ancestor($distance)->values[$name];
    }

    /**
     * Get a variable's value from this or enclosing environments
     * @param Token $name The variable name token
     * @return mixed The variable's value
     * @throws RuntimeError if variable not found
     */
    public function get(Token $name) {
        if(in_array($name->lexeme, array_keys($this->values))) {
            return $this->values[$name->lexeme];
        }

        if($this->enclosing !== null) return $this->enclosing->get($name);

        throw new RuntimeError($name, "Undefined variable '". $name->lexeme ."'.");
    }

    /**
     * Assign a value to an existing variable in this or enclosing environments
     * @param Token $name The variable name token
     * @param mixed $value The value to assign
     * @throws RuntimeError if variable not found
     */
    public function assign(Token $name, $value)
    {
        if(in_array($name->lexeme,array_keys($this->values)))
        {
            $this->values[$name->lexeme]= $value;
            return; 
        }

        if($this->enclosing != null){
            $this->enclosing->assign($name, $value);
            return;
        }
          
        throw new RuntimeError($name, "Undefined variable '".$name->lexeme."'.");
    }
}