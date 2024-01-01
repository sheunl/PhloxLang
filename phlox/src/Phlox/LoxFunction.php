<?php
namespace Phlox;

use Phlox\Stmt\Function_;

class LoxFunction implements LoxCallable
{
   public function __construct(private Function_ $declaration, private Environment $closure, private bool $isInitializer){}

   function bind(LoxInstance $instance){
    $environment = new Environment($this->closure);
    $environment->define("this", $instance);
    return new LoxFunction($this->declaration, $environment, $this->isInitializer);
    }

   public function call (Interpreter $interpreter, array $arguments)
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

   public function arity(): int
   {
    return count($this->declaration->params);
   }

   public function __toString()
   {
    return "<fn ".$this->declaration->name->lexeme.">";
   }

}