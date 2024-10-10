<?php

namespace Phlox;

class Tools 
{
    public static function logFunction(string $functionName):void
    {
        echo $functionName." Called\n";
    } 
}