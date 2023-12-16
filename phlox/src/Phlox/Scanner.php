<?php
namespace Phlox;

use Phlox\Token;
use Phlox\TokenType;
use Phlox\Phlox;

class Scanner{
    private $source;
    private $tokens = array();

    private $start = 0;
    private $current = 0;
    private $line = 0; 

    private static $keywords= [
        "and" => TokenType::AND,
        "class" => TokenType::ACLASS,
        "else" => TokenType::ELSE,
        "false" => TokenType::FALSE,
        "for" => TokenType::FOR,
        "fun" => TokenType::FUN,
        "if" => TokenType::IF,
        "nil" => TokenType::NIL,
        "or" => TokenType::OR,
        "print" => TokenType::PRINT,
        "return" => TokenType::RETURN,
        "super" => TokenType::SUPER,
        "this" => TokenType::THIS,
        "true" => TokenType::TRUE,
        "var" => TokenType::VAR,
        "while" => TokenType::WHILE,
    ];
    


    function __construct($source)
    {
        $this->source = $source;
    }

    function scanTokens(){
        while(!$this->isAtEnd()){
            $this->start = $this->current;
            $this->scanToken();
        }

        array_push($this->tokens,new Token(TokenType::EOF,"",null,$this->line));
        return $this->tokens;
    }

    function scanToken(){
        $c = $this->advance();
        switch($c){
            case '(': $this->addToken(TokenType::LEFT_PAREN); break;
            case ')': $this->addToken(TokenType::RIGHT_PAREN); break;
            case '{': $this->addToken(TokenType::LEFT_BRACE); break;
            case '}': $this->addToken(TokenType::RIGHT_BRACE); break;
            case ',': $this->addToken(TokenType::COMMA); break;
            case '.': $this->addToken(TokenType::DOT); break;
            case '-': $this->addToken(TokenType::MINUS); break;
            case '+': $this->addToken(TokenType::PLUS); break;
            case ';': $this->addToken(TokenType::SEMICOLON); break;
            case '*': $this->addToken(TokenType::STAR); break;  
            case '!': $this->addToken($this->match('!') ? TokenType::BANG_EQUAL : TokenType::BANG); break;
            case '=': $this->addToken($this->match('=') ? TokenType::EQUAL_EQUAL : TokenType::EQUAL); break;
            case '<': $this->addToken($this->match('=') ? TokenType::LESS_EQUAL : TokenType::LESS); break;
            case '>': $this->addToken($this->match('=') ? TokenType::GREATER_EQUAL : TokenType::GREATER); break;
            case '/': 
                if($this->match('/')){
                    while ($this->peek() != '\n' && !$this->isAtEnd()) $this->advance(); 
                    break;
                } else{
                    $this->addToken(TokenType::SLASH);
                }
                break;
            case ' ':
            case '\r':
            case '\t':
                break;
            
            case '\n':
                $this->line++;
                break;

            case '"': $this->string(); break;

            default:
                if($this->isDigit($c)){
                    $this->number();
                } else if($this->isAlpha($c)){
                    $this->identifier();
                }
                else {
                    Phlox::error($this->line,"Unexpected Character.");
                }
                break;
        }
    }

    private function identifier(){
        while($this->isAlphaNumeric($this->peek())) $this->advance();
        // $this->addToken(TokenType::IDENTIFIER);
        $text = substr($this->source,$this->start, $this->current);
        $type = in_array($text,self::$keywords)?self::$keywords[$text]:null;
        if ($type == null) $type = TokenType::IDENTIFIER;
        $this->addToken($type);
    }

    private function isAlpha($c){
        return ($c >= 'a' && $c <= 'z') || ($c >= 'A' && $c <= 'Z') || $c == '_';
    }

    private function isAlphaNumeric($c){
        return $this->isAlpha($c) || $this->isDigit($c);
    }

    private function number(){
        while ($this->isDigit($this->peek())) $this->advance();

        if ($this->peek() == '.' && $this->isDigit($this->peekNext())){
            $this->advance();

            while ($this->isDigit($this->peek())) $this->advance();
        }

        $this->addToken(TokenType::NUMBER, doubleval(substr($this->source, $this->start, $this->current - 1 )));
    }

    private function string(){
        while($this->peek() != '"' && !$this->isAtEnd()){
            if ($this->peek() == '\n') $this->line++;
            $this->advance();
        }

        if ($this->isAtEnd()){
            Phlox::error($this->line, "Unterminated string.");
            return;
        }

        $this->advance();

        $value = substr($this->source, $this->start + 1, $this->current - 2);
        $this->addToken(TokenType::STRING, $value);

    }

    private function match ($expected){
        if($this->isAtEnd()) return false;
        if($this->source[$this->current] != $expected ) return false;

        $this->current++;
        return true;
    }

    private function peek(){
        if($this->isAtEnd()) return '\0';
        return $this->source[$this->current];
    }

    private function peekNext(){
        if($this->current + 1 >= strlen($this->source)) return '\0';
        return $this->source[$this->current + 1];
    }

    private function isDigit($c){
        return $c >= '0' && $c <= '9';
    }

    private function isAtEnd(){
        return $this->current >= strlen($this->source);
    }

    private function advance(){
        return $this->source[$this->current++];
    }

    private function addToken($type){
        $this->addToken_G($type, null);
    }

    private function addToken_G($type, $literal)
    {
        $text =  substr($this->source,$this->start,$this->current - $this->start);
        array_push($this->tokens,new Token($type,$text,null,$this->line));
    }
}