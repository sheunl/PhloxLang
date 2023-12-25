<?php
namespace Phlox;

class Environment 
{
    private $values = [];

    public function __construct(private $enclosing = null){}

    public function define(string $name, $value)
    {
        $this->values[$name] = $value;
    }

    public function get(Token $name) {
        if(in_array($name->lexeme, array_keys($this->values))) {
            return $this->values[$name->lexeme];
        }

        if($this->enclosing !== null) return $this->enclosing->get($name);

        throw new RuntimeError($name, "Undefined variable '". $name->lexeme ."'.");
    }

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