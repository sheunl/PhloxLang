<?php
namespace Phlox;

class Environment 
{
    private $values = [];

    public function define(string $name, $value)
    {
        $this->values[$name] = $value;
    }

    public function get(Token $name) {
        if(in_array($name->lexeme, array_keys($this->values))) {
            return $this->values[$name->lexeme];
        }

        throw new RuntimeError($name, "Undefined variable '". $name->lexeme ."'.");
    }

    public function assign(Token $name, $value)
    {
        if(in_array($name->lexeme,array_keys($this->values)))
        {
            $this->values[$name->lexeme]= $value;
            return; 
        }
          
        throw new RuntimeError($name, "Undefined variable '".$name->lexeme."'.");
    }


}