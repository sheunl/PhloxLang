<?php

namespace Phlox;

use Phlox\DS\Map;

class LoxClass implements LoxCallable
{
    public function __construct(public string $name, public ?LoxClass $superclass, private Map $methods){}

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

    public function __toString():string
    {
        return $this->name;
    }

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

    public function arity():int
    {
        $initializer = $this->findMethod("init");
        if ($initializer == null) return 0;

        return $initializer->arity();
    }
}