<?php

namespace Phlox;

class LoxClass implements LoxCallable
{
    public function __construct(public string $name){}

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