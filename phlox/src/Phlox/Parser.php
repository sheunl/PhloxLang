<?php

use Phlox\TokenType;

class Parser{
    private TokenType $tokens;
    private int $current = 0;

    public function __construct(TokenType $tokens)
    {
        $this->tokens = $tokens;
    }

    private function expression()
    {
        // return equality();
    }

    private function equality()
    {
        // $expr = $this->comparison();

        // while(match(TokenType::BANG_EQUAL, TokenType::EQUAL_EQUAL)){
        //     $operator = previous();
        //     $right = comparison();
        //     $expr = new 
        // }
    }

}