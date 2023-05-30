<?php
namespace Phlox;


class Token {

    private $type;
    private $lexeme;
    private $literal;
    private $line;
     
    function __construct($type, $lexeme, $literal, $line)
    {
        $this->type = $type;
        $this->lexeme = $lexeme;
        $this->literal = $literal;
        $this->line = $line;
    }

    public function __toString():string
    {
        return strval($this->type . " " . $this->lexeme . " ".$this->literal);
    }
    
}

?>