<?php
/**
 * Scanner class handles lexical analysis of source code
 * It breaks down the source text into a sequence of tokens
 * that can be used by the parser
 */
namespace Phlox;

use Phlox\Token;
use Phlox\TokenType;
use Phlox\Phlox;

/**
 * Scanner/Lexer for the Lox language
 * Converts source code into tokens
 */
class Scanner
{
    /**
     * Source code being scanned
     * @var string
     */
    private string $source;

    /**
     * List of tokens generated from source
     * @var array
     */
    private array $tokens = [];

    /** Map of reserved keywords to their token types */
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
    
    /**
     * Current position in source code
     * @var int
     */
    private int $start = 0;

    /**
     * Current character being examined
     * @var int
     */
    private int $current = 0;

    /**
     * Current line number in source
     * @var int
     */
    private int $line = 1;


    /**
     * Create a new scanner
     * @param string $source Source code to scan
     */
    public function __construct(string $source)
    {
        $this->source = $source;
    }

    /**
     * Scan source code and return list of tokens
     * @return array List of Token objects
     */
    public function scanTokens():array
    {
        while(!$this->isAtEnd()){
            $this->start = $this->current;
            $this->scanToken();
        }

        array_push($this->tokens,new Token(TokenType::EOF,"",null,$this->line));
        return $this->tokens;
    }

    /**
     * Scans a single token from the source code
     * Identifies the token type and adds it to the tokens array
     */
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
            case '!': $this->addToken($this->match('=') ? TokenType::BANG_EQUAL : TokenType::BANG); break;
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
            case "\r":
            case "\t":
                break;
            
            case "\n":
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

    /**
     * Processes an identifier (variable name, keyword, etc.)
     */
    private function identifier(){
        while($this->isAlphaNumeric($this->peek())) $this->advance();
        $text = trim(substr($this->source, $this->start, $this->current - $this->start));
        $type = in_array($text, array_keys(self::$keywords)) ? self::$keywords[$text] : null;
        if ($type == null) $type = TokenType::IDENTIFIER;
        $this->addToken($type);
    }

    /**
     * Checks if a character is alphabetic or underscore
     * @param string $c Character to check
     * @return bool True if alphabetic or underscore
     */
    private function isAlpha($c){
        return ($c >= 'a' && $c <= 'z') || ($c >= 'A' && $c <= 'Z') || $c == '_';
    }

    /**
     * Checks if a character is alphanumeric or underscore
     * @param string $c Character to check
     * @return bool True if alphanumeric or underscore
     */
    private function isAlphaNumeric($c){
        return $this->isAlpha($c) || $this->isDigit($c);
    }

    /**
     * Processes a numeric literal
     */
    private function number(){
        while ($this->isDigit($this->peek())) $this->advance();

        if ($this->peek() == '.' && $this->isDigit($this->peekNext())){
            $this->advance();
            while ($this->isDigit($this->peek())) $this->advance();
        }

        $this->addToken_G(TokenType::NUMBER, doubleval(substr($this->source, $this->start, $this->current)));
    }

    /**
     * Processes a string literal
     */
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
        $value = substr($this->source, $this->start + 1, $this->current - $this->start -2  );
        $this->addToken_G(TokenType::STRING, $value);
    }

    /**
     * Checks if the next character matches expected
     * @param string $expected The expected character
     * @return bool True if matches, false otherwise
     */
    private function match($expected){
        if($this->isAtEnd()) return false;
        if($this->source[$this->current] != $expected ) return false;

        $this->current++;
        return true;
    }

    /**
     * Looks at the current character without consuming it
     * @return string The current character
     */
    private function peek(){
        if($this->isAtEnd()) return '\0';
        return $this->source[$this->current];
    }

    /**
     * Looks at the next character without consuming it
     * @return string The next character
     */
    private function peekNext(){
        if($this->current + 1 >= strlen($this->source)) return '\0';
        return $this->source[$this->current + 1];
    }

    /**
     * Checks if a character is a digit
     * @param string $c Character to check
     * @return bool True if digit
     */
    private function isDigit($c){
        return $c >= '0' && $c <= '9';
    }

    /**
     * Checks if we've reached the end of source
     * @return bool True if at end
     */
    private function isAtEnd(){
        return $this->current >= strlen($this->source);
    }

    /**
     * Consumes the current character and returns it
     * @return string The current character
     */
    private function advance(){
        return $this->source[$this->current++];
    }

    /**
     * Adds a token without a literal value
     * @param string $type Token type from TokenType constants
     */
    private function addToken($type){
        $this->addToken_G($type, null);
    }

    /**
     * Adds a token with a literal value
     * @param string $type Token type from TokenType constants
     * @param mixed $literal Literal value for the token
     */
    private function addToken_G($type, $literal)
    {
        $text = substr($this->source,$this->start,$this->current - $this->start);
        array_push($this->tokens,new Token($type,$text,$literal,$this->line));
    }
}