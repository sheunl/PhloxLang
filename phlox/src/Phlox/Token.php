<?php
namespace Phlox;

/**
 * Token represents a lexical token in the source code
 * Each token has a type, lexeme (actual text), literal value, and line number
 */
class Token {

    public $type;
    public $lexeme;
    public $literal;
    public $line;
     
    /**
     * Creates a new Token instance
     * @param string $type The token type from TokenType constants
     * @param string $lexeme The actual text of the token
     * @param mixed $literal The literal value for numbers, strings, etc.
     * @param int $line The line number where the token appears
     */
    function __construct($type, $lexeme, $literal, $line)
    {
        $this->type = $type;
        $this->lexeme = $lexeme;
        $this->literal = $literal;
        $this->line = $line;
    }

    /**
     * Converts the token to its string representation
     * @return string The token's type, lexeme and literal value concatenated
     */
    public function __toString():string
    {
        return strval($this->type . " " . $this->lexeme . " ".$this->literal);
    }
    
}

