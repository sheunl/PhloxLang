<?php

namespace Phlox;

use Phlox\DS\Map;

class LoxInstance
{

    private Map $fields;

    private function getFields():Map
    {
        if (! isset($this->fields)){
            $this->fields = new Map();
        }

        return $this->fields;
    }

    public function __construct(private LoxClass $klass)
    {
        
    }

    function get(Token $name)
    {
        if ($this->getFields()->hasKey($name->lexeme)){
            return $this->getFields()->get($name->lexeme);
        }

        $method = $this->klass->findMethod($name->lexeme);
        if($method != null) return $method;

        throw new RuntimeError($name, "Undefined property '". $name->lexeme. "'.");
    }

    function set (Token $name, object $value)
    {
        $this->fields->put($name->lexeme, $value);
    }

    public function __toString()
    {
        return $this->klass->name . " instance";
    }
}