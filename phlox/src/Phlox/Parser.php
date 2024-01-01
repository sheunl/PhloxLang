<?php
namespace Phlox;

use Phlox\Expr\Assign;
use Phlox\Expr\Binary;
use Phlox\Expr\Call;
use Phlox\TokenType;
use Phlox\Expr\Expr;
use Phlox\Expr\Get;
use Phlox\Expr\Grouping;
use Phlox\Expr\Literal;
use Phlox\Expr\Logical;
use Phlox\Expr\Set;
use Phlox\Expr\Unary;
use Phlox\Expr\Variable;
use Phlox\Phlox;
use Phlox\Stmt\Block;
use Phlox\Stmt\Expression;
use Phlox\Stmt\Function_;
use Phlox\Stmt\If_;
use Phlox\Stmt\Printr;
use Phlox\Return_;
use Phlox\Stmt\AClass;
use Phlox\Stmt\ReturnR;
use Phlox\Stmt\Stmt;
use Phlox\Stmt\Var_;
use Phlox\Stmt\While_;
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
        return $this->assignment();
    }

    private function declaration()
    {
      try{
        if ($this->match(TokenType::ACLASS)) return $this->classDeclaration();
        if($this->match(TokenType::FUN)) return $this->aFunction("function");
        if($this->match(TokenType::VAR)) return $this->varDeclaration();

        return $this->statement();
      } catch (ParzerError $error)
      {
        $this->synchronize();
        return null;
      }
    }

    private function classDeclaration()
    {
      $name = $this->consume(TokenType::IDENTIFIER, "Expect class name");
      $this->consume(TokenType::LEFT_BRACE, "Expect '{' before class body.");
      
      $methods = [];

      while(! $this->check(TokenType::RIGHT_BRACE) && ! $this->isAtEnd()){
        $methods[] = $this->aFunction("method");
      }

      $this->consume(TokenType::RIGHT_BRACE, "Expect '}' after class body.");

      return new AClass($name, $methods);
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

    private function whileStatement():Stmt
    {
      $this->consume(TokenType::LEFT_PAREN, "Expect '(' after 'while'.");
      $condition = $this->expression();
      $this->consume(TokenType::RIGHT_PAREN, "Expect ')' after condition.");
      $body = $this->statement();

      return new While_($condition, $body);
    }

    private function statement(): Stmt
    {
      if ($this->match(TokenType::IF)) return $this->ifStatement();
      if ($this->match(TokenType::PRINT)) return $this->printStatement();
      if ($this->match(TokenType::RETURN)) return $this->returnStatement();
      if ($this->match(TokenType::LEFT_BRACE)) return new Block($this->block());
      if ($this->match(TokenType::WHILE)) return $this->whileStatement();
      if ($this->match(TokenType::FOR)) return $this->forStatement();

      return $this->expressionStatement();
    }

    private function forStatement()
    {
      $this->consume(TokenType::LEFT_PAREN, "Expect '('after 'for'.");

      $intializer = null;

      if ($this->match(TokenType::SEMICOLON))
      {
        $intializer = null;
      } else if ($this->match(TokenType::VAR))
      {
        $intializer = $this->varDeclaration();
      } else {
        $intializer = $this->expressionStatement();
      }

      $condition = null;

      if(! $this->check(TokenType::SEMICOLON)) {
        $condition = $this->expression();
      }

      $this->consume(TokenType::SEMICOLON, "Expect ';' after loop condition.");

      $increment = null;

      if (!$this->check(TokenType::RIGHT_PAREN)){
        $increment = $this->expression();
      }

      $this->consume(TokenType::RIGHT_PAREN, "Expect ')' after for clause.");

      $body = $this->statement();

      if ($increment != null){
        $body =  new Block( [$body, new Expression($increment)]);
      }

      if ($condition === null) $condition =  new Literal(true);

      $body = new While_($condition, $body);

      if ($intializer !== null){
        $body = new Block([$intializer, $body]);
      }

      return $body;
    }

    private function ifStatement() : Stmt {
      $this->consume(TokenType::LEFT_PAREN, "Expect '(' after 'if'.");
      $condition = $this->expression();
      $this->consume(TokenType::RIGHT_PAREN, "Expect ')' after if condition.");

      $thenBranch = $this->statement();
      $elseBranch = null;
      if ($this->match(TokenType::ELSE)){
        $elseBranch = $this->statement();
      }

      return new If_($condition, $thenBranch, $elseBranch);
    }

    private function block()
    {
      $statements = [];

      while (! $this->check(TokenType::RIGHT_BRACE) && !$this->isAtEnd()) {
        $statements[] = $this->declaration();
      }

      $this->consume(TokenType::RIGHT_BRACE, "Expect '}' after block.");

      return $statements;
    }

    private function printStatement()
    {
      $value = $this->expression();
      $this->consume(TokenType::SEMICOLON, "Expect ';' after value.");
      return new Printr($value);
    }

    private function returnStatement()
    {
      $keyword = $this->previous();
      $value = null;

      if (! $this->check(TokenType::SEMICOLON)){
        $value =  $this->expression();
      }

      $this->consume(TokenType::SEMICOLON, "Expect ';' after return value.");

      return new ReturnR($keyword, $value);
    }

    private function expressionStatement() : Stmt 
    {
      $expr = $this->expression();
      $this->consume(TokenType::SEMICOLON, "Expect ';' after expression.");
      return new Expression($expr);
    }

    private function aFunction(string $kind)
    {
      $name = $this->consume(TokenType::IDENTIFIER, "Expect ". $kind." name.");
      $this->consume(TokenType::LEFT_PAREN, "Expect '(' after ".$kind." name.");
      $parameter = [];

      if(! $this->check(TokenType::RIGHT_PAREN)){
        do {
          if (count($parameter) >= 255){
            $this->error($this->peek(), "Can't have more than 255 parameters.");
          }

          $parameter[] = $this->consume(TokenType::IDENTIFIER, "Expect parameter name.");
        } while($this->match(TokenType::COMMA));
      }
      
      $this->consume(TokenType::RIGHT_PAREN, "Expect ')' after parameters.");

      $this->consume(TokenType::LEFT_BRACE, "Expect '{' before ".$kind." body.");

      $body = $this->block();

      return new Function_($name, $parameter, $body);
    }

    private function assignment() : Expr{
      // $expr = $this->equality();
      $expr = $this->or();

      if ($this->match(TokenType::EQUAL)){
        $equals = $this->previous();
        $value = $this->assignment();

        if (get_class($expr) === "Phlox\Expr\Variable"){
          $name = ($expr->name);
          return new Assign($name, $value);
        } else if ($expr instanceof Get) {
          $get = $expr;
          return new Set($get->object, $get->name, $value);
        }

        $this->error($equals, "Invalid assingement target.");
      }

      return $expr;
    }

    private function or(): Expr 
    {
      $expr = $this->and();

      while($this->match(TokenType::OR)){
        $operator = $this->previous();
        $right = $this->and();
        $expr = new Logical($expr, $operator, $right);
      }

      return $expr;
    }

    private function and(): Expr
    {
      $expr = $this->equality();

      while ($this->match(TokenType::AND)){
        $operator = $this->previous();
        $right = $this->equality();
        $expr = new Logical( $expr, $operator, $right);
      }

      return $expr;
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

      return $this->call();
    }

    private function finishCall (Expr $callee)
    {
      $arguments = [];

      if(! $this->check(TokenType::RIGHT_PAREN)){
        do {
          if (count($arguments) >= 255){
            $this->error($this->peek(), "Can't have more than 255 arguments.");
          }
          $arguments[] = $this->expression();
        } while ($this->match(TokenType::COMMA));
      }

      $paren = $this->consume(TokenType::RIGHT_PAREN, "Expect ')' after arguments.");

      return new Call($callee, $paren, $arguments);
    }

    private function call():Expr
    {
      $expr  = $this->primary();

      while(true){
        if($this->match(TokenType::LEFT_PAREN)) {
          $expr = $this->finishCall($expr);
        } else if ($this->match(TokenType::DOT)){
          $name = $this->consume(TokenType::IDENTIFIER, "Expect property name after '.'.");
          $expr = new Get($expr, $name);
        }
        else {
          break;
        }
      }

      return $expr;
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