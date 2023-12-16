<?php
namespace Phlox;

use Phlox\Expr\Binary;
use Phlox\TokenType;
use Phlox\Expr\Expr;
use Phlox\Expr\Grouping;
use Phlox\Expr\Literal;
use Phlox\Expr\Unary;
use Phlox\Expr\Variable;
use Phlox\Phlox;
use Phlox\Stmt\Expression;
use Phlox\Stmt\Printr;
use Phlox\Stmt\Stmt;
use Phlox\Stmt\Var_;
use Phlox\Token;
// use PhpCsFixer\Fixer\PhpTag\EchoTagSyntaxFixer;

// use function PHPSTORM_META\type;

class Parser{
    private array $tokens;
    private int $current = 0;

    public function __construct(array $tokens)
    {
        $this->tokens = $tokens;
    }

    public function parse(){
      $statements = [];
      while(!$this->isAtEnd()){
        $statements[] = $this->declaration();

      }

      return $statements;
      // try{
      //   return $this->expression();
      // } catch (ParzerError $error){
      //   return null;
      // }
    }


    private function expression():Expr
    {
        return $this->equality();
    }

    private function declaration()
    {
      try{
        if($this->match(TokenType::VAR)) return $this->varDeclaration();

        return $this->statement();
      } catch (ParzerError $error)
      {
        $this->synchronize();
        return null;
      }
    }

    private function varDeclaration() {
      $name = $this->consume(TokenType::IDENTIFIER, "Expect variable name.");

      $initializer = null;
      
      if ($this->match(TokenType::EQUAL)){
        $initializer = $this->expression();
      }

      $this->consume(TokenType::SEMICOLON, "Expect ';' after variable declaration.");
      return new Var_($name, $initializer);
    }

    private function statement(): Stmt
    {
      if ($this->match(TokenType::PRINT)) return $this->printStatement();

      return $this->expressionStatement();
    }

    private function printStatement()
    {
      $value = $this->expression();
      $this->consume(TokenType::SEMICOLON, "Expect ';' after value.");
      return new Printr($value);
    }

    private function expressionStatement() : Stmt 
    {
      $expr = $this->expression();
      $this->consume(TokenType::SEMICOLON, "Expect ';' after expression.");
      return new Expression($expr);
    }

    private function equality():Expr
    {
        $expr = $this->comparison();

        while($this->match(TokenType::BANG_EQUAL, TokenType::EQUAL_EQUAL)){
            $operator = $this->previous();
            $right = $this->comparison();
            $expr = new Binary($expr,$operator,$right);
        }

        return $expr;
    }

    private function comparison():Expr{
      $expr = $this->term();

      while($this->match(TokenType::GREATER, TokenType::GREATER_EQUAL, TokenType::LESS, TokenType::LESS_EQUAL)){
        $operator = $this->previous();
        $right = $this->term();
        $expr = new Binary($expr, $operator, $right);
      }

      return $expr;
    }

    private function term():Expr
    {
      $expr = $this->factor();

      while($this->match(TokenType::MINUS, TokenType::PLUS)) {
        $operator = $this->previous();
        $right = $this->factor();
        $expr = new Binary($expr, $operator, $right);
      }

      return $expr;
    }

    private function factor():Expr
    {
      $expr = $this->unary();

      while($this->match(TokenType::SLASH, TokenType::STAR)){
        $operator = $this->previous();
        $right = $this->unary();
        $expr = new Binary($expr, $operator, $right);
      }

      return $expr;
    }

    private function unary() : Expr {
      if($this->match(TokenType::BANG, TokenType::MINUS)){
        $operator = $this->previous();
        $right = $this->unary();
        return new Unary($operator, $right);
      }

      return $this->primary();
    }

    private function primary() : Expr {
      if ($this->match(TokenType::FALSE)) return new Literal(false);
      if ($this->match(TokenType::TRUE)) return new Literal(true);
      if ($this->match(TokenType::NIL)) return new Literal(null);

      if($this->match(TokenType::NUMBER, TokenType::STRING)){
        return new Literal($this->previous()->literal);
      }

      if($this->match(TokenType::IDENTIFIER)){
        return new Variable($this->previous());
      }

      if($this->match(TokenType::LEFT_PAREN)){
        $expr = $this->expression();
        $this->consume(TokenType::RIGHT_BRACE, "Expect ')' after expression,");
        return new Grouping($expr);
      }

      throw $this->error($this->peek(), "Expect expression.\n");
    }

    private function consume(string $type, string $message){
      if($this->check($type)) return $this->advance();

      throw $this->error($this->peek(), $message);
    }

    private function error(Token $token, string $message):ParzerError{
      Phlox::error_($token, $message);
      return new ParzerError();
    }

    private function synchronize(){
      $this->advance();

      while(! $this->isAtEnd()){
        if ($this->previous()->type == TokenType::SEMICOLON) return;

        switch ($this->peek()->type){
          case TokenType::ACLASS:
          case TokenType::FUN:
          case TokenType::VAR:
          case TokenType::FOR:
          case TokenType::IF:
          case TokenType::WHILE:
          case TokenType::PRINT:
          case TokenType::RETURN:
            return;
          }

          $this->advance();
      }
    }

    private function match(string ...$types) : bool 
    {
      foreach($types as $type){
        if($this->check($type)){
          $this->advance();
          return true;
        }
      }

      return false;
    }

    private function check(string $type):bool
    {
      if($this->isAtEnd()) return false;
      return $this->peek()->type === $type;
    }

    private function advance():Token
    {
      if(! $this->isAtEnd()) $this->current++;
      return $this->previous();
    }

    private function isAtEnd():bool
    {
      return $this->peek()->type == TokenType::EOF;
    }

    private function peek():Token
    {
      return $this->tokens[$this->current];
    }

    private function previous():Token
    {
      return $this->tokens[$this->current -1];
    }


    /*

 private Expr unary() {
    if (match(BANG, MINUS)) {
      Token operator = previous();
      Expr right = unary();
      return new Expr.Unary(operator, right);
    }

     private Token consume(TokenType type, String message) {
    if (check(type)) return advance();

    throw error(peek(), message);
  }
//< consume
//> check
  private boolean check(TokenType type) {
    if (isAtEnd()) return false;
    return peek().type == type;
  }
//< check
//> advance
  private Token advance() {
    if (!isAtEnd()) current++;
    return previous();
  }
//< advance
//> utils
  private boolean isAtEnd() {
    return peek().type == EOF;
  }

  private Token peek() {
    return tokens.get(current);
  }

  private Token previous() {
    return tokens.get(current - 1);
  }
    */

    // private function unary(){
    //     if()
    // }


    // private function advance()

    
    /** Utilities for the Parser */

    /* --------------- */
}

class ParzerError extends \RuntimeException{}