<?php

namespace Phlox;

use RuntimeException;

class Return_ extends RuntimeException
{
    public $value;

    public function __construct($value)
    {
        parent::__construct("");

        $this->value = $value;
        
    }
}