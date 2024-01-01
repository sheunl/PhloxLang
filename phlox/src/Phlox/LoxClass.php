<?php

namespace Phlox;

use Phlox\DS\Map;

class LoxClass implements LoxCallable
{
    public function __construct(public string $name, private Map $methods){}

    public function findMethod(string $name)
    {
        if($this->methods->hasKey($name)){
            return $this->methods->get($name);
        }

        return null;
    }

    public function __toString():string
    {
        return $this->name;
    }

    public function call(Interpreter $interpreter, array $argument)
    {
        $instance = new LoxInstance($this);
        return $instance;
    }

    public function arity():int
    {
        return 0;
    }
}