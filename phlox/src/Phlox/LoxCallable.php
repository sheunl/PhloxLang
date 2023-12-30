<?php
namespace Phlox;

interface LoxCallable {
    function arity():int;
    function call(Interpreter $interpreter, array $arguments);
}