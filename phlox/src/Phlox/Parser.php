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
use Phlox\Expr\Super;
use Phlox\Expr\This;
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


/**
 * Parser class handles the parsing of tokens into an abstract syntax tree
 * It implements recursive descent parsing for the Phlox language
 */
class Parser{
    /**
     * Array of tokens to be parsed
     * @var array
     */
    private array $tokens;
    
    /**
     * Current position in the token array
     * @var int
     */
    private int $current = 0;

    /**
     * Initialize parser with array of tokens
     * @param array $tokens Array of Token objects to parse
     */
    public function __construct(array $tokens)
    {
        $this->tokens = $tokens;
    }

    /**
     * Parse tokens into array of statements
     * @return array Array of parsed statements
     */
    public function parse(){
      $statements = [];
      while(!$this->isAtEnd()){
        $statements[] = $this->declaration();

      }

      return $statements;
   
    }

    /**
     * Parse an expression
     * @return Expr The parsed expression
     */
    private function expression():Expr
    {
        return $this->assignment();
    }

    /**
     * Parse a declaration statement
     * @return Stmt|null The parsed declaration statement
     */
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

    /**
     * Parse a class declaration
     * @return AClass The parsed class declaration
     */
    private function classDeclaration()
    {
      $name = $this->consume(TokenType::IDENTIFIER, "Expect class name");

      $superclass = null;
      if ($this->match(TokenType::LESS)){
        $this->consume(TokenType::IDENTIFIER, "Expect superclass name.");
        $superclass = new Variable($this->previous());
      }

      $this->consume(TokenType::LEFT_BRACE, "Expect '{' before class body.");
      
      $methods = [];

      while(! $this->check(TokenType::RIGHT_BRACE) && ! $this->isAtEnd()){
        $methods[] = $this->aFunction("method");
      }

      $this->consume(TokenType::RIGHT_BRACE, "Expect '}' after class body.");

      return new AClass($name, $superclass, $methods);
    }

    /**
     * Parse a variable declaration
     * @return Var_ The parsed variable declaration
     */
    private function varDeclaration() {
      $name = $this->consume(TokenType::IDENTIFIER, "Expect variable name.");

      $initializer = null;
      
      if ($this->match(TokenType::EQUAL)){
        $initializer = $this->expression();
      }

      $this->consume(TokenType::SEMICOLON, "Expect ';' after variable declaration.");
      return new Var_($name, $initializer);
    }

    /**
     * Parse a while statement
     * @return Stmt The parsed while statement
     */
    private function whileStatement():Stmt
    {
      $this->consume(TokenType::LEFT_PAREN, "Expect '(' after 'while'.");
      $condition = $this->expression();
      $this->consume(TokenType::RIGHT_PAREN, "Expect ')' after condition.");
      $body = $this->statement();

      return new While_($condition, $body);
    }

    /**
     * Parse a statement
     * @return Stmt The parsed statement
     */
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

    /**
     * Parse a for statement and convert it to equivalent while statement
     * @return Stmt The parsed for statement as a while statement
     */
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

    /**
     * Parse an if statement
     * @return Stmt The parsed if statement
     */
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

    /**
     * Parse a block of statements
     * @return array Array of parsed statements
     */
    private function block()
    {
      $statements = [];

      while (! $this->check(TokenType::RIGHT_BRACE) && !$this->isAtEnd()) {
        $statements[] = $this->declaration();
      }

      $this->consume(TokenType::RIGHT_BRACE, "Expect '}' after block.");

      return $statements;
    }

    /**
     * Parse a print statement
     * @return Printr The parsed print statement
     */
    private function printStatement()
    {
      $value = $this->expression();
      $this->consume(TokenType::SEMICOLON, "Expect ';' after value.");
      return new Printr($value);
    }

    /**
     * Parse a return statement
     * @return ReturnR The parsed return statement
     */
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

    /**
     * Parse an expression statement
     * @return Stmt The parsed expression statement
     */
    private function expressionStatement() : Stmt 
    {
      $expr = $this->expression();
      $this->consume(TokenType::SEMICOLON, "Expect ';' after expression.");
      return new Expression($expr);
    }

    /**
     * Parse a function declaration
     * @param string $kind Type of function being parsed ("function" or "method")
     * @return Function_ The parsed function declaration
     */
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

    /**
     * Parse an assignment expression
     * @return Expr The parsed assignment expression
     */
    private function assignment() : Expr{
      // $expr = $this->equality();
      $expr = $this->or();

      if ($this->match(TokenType::EQUAL)){
        $equals = $this->previous();
        $value = $this->assignment();

        if ($expr instanceof Variable) {
          return new Assign($expr->name, $value);
        } else if ($expr instanceof Get) {
          $get = $expr;
          return new Set($get->object, $get->name, $value);
        }

        $this->error($equals, "Invalid assingement target.");
      }

      return $expr;
    }

    /**
     * Parse a logical OR expression
     * @return Expr The parsed logical OR expression
     */
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

    /**
     * Parse a logical AND expression
     * @return Expr The parsed logical AND expression
     */
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

    /**
     * Parse an equality expression
     * @return Expr The parsed equality expression
     */
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

    /**
     * Parse a comparison expression
     * @return Expr The parsed comparison expression
     */
    private function comparison():Expr{
      $expr = $this->term();

      while($this->match(TokenType::GREATER, TokenType::GREATER_EQUAL, TokenType::LESS, TokenType::LESS_EQUAL)){
        $operator = $this->previous();
        $right = $this->term();
        $expr = new Binary($expr, $operator, $right);
      }

      return $expr;
    }

    /**
     * Parse a term (addition/subtraction) expression
     * @return Expr The parsed term expression
     */
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

    /**
     * Parse a factor (multiplication/division) expression
     * @return Expr The parsed factor expression
     */
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

    /**
     * Parse a unary expression
     * @return Expr The parsed unary expression
     */
    private function unary() : Expr {
      if($this->match(TokenType::BANG, TokenType::MINUS)){
        $operator = $this->previous();
        $right = $this->unary();
        return new Unary($operator, $right);
      }

      return $this->call();
    }

    /**
     * Complete parsing of a function call
     * @param Expr $callee The function being called
     * @return Call The parsed function call expression
     */
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

    /**
     * Parse a function call expression
     * @return Expr The parsed call expression
     */
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

    /**
     * Parse a primary expression (literals, groupings, etc.)
     * @return Expr The parsed primary expression
     */
    private function primary() : Expr {
      
      if ($this->match(TokenType::FALSE)) return new Literal(false);
      if ($this->match(TokenType::TRUE)) return new Literal(true);
      if ($this->match(TokenType::NIL)) return new Literal(null);

      if($this->match(TokenType::NUMBER, TokenType::STRING)){
        return new Literal($this->previous()->literal);
      }

      if ($this->match(TokenType::SUPER)){
        $keyword = $this->previous();
        $this->consume(TokenType::DOT, "Expect '.' after 'super'.");
        $method = $this->consume(TokenType::IDENTIFIER, "Expect superclass method name.");
        return new Super($keyword, $method);
      }

      if ($this->match(TokenType::THIS)) return new This($this->previous());

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

    /**
     * Consume a token of expected type or throw error
     * @param string $type Expected token type
     * @param string $message Error message if token doesn't match
     * @return Token The consumed token
     * @throws ParzerError if token doesn't match expected type
     */
    private function consume(string $type, string $message){
      if($this->check($type)) return $this->advance();

      throw $this->error($this->peek(), $message);
    }

    /**
     * Create a parser error
     * @param Token $token Token where error occurred
     * @param string $message Error message
     * @return ParzerError The created error
     */
    private function error(Token $token, string $message):ParzerError{
      Phlox::error_($token, $message);
      return new ParzerError();
    }

    /**
     * Synchronize parser state after error
     * Discards tokens until a statement boundary is found
     */
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

    /**
     * Check if current token matches any of given types
     * @param string ...$types Token types to match
     * @return bool True if current token matches any type
     */
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

    /**
     * Check if current token is of given type
     * @param string $type Token type to check
     * @return bool True if current token matches type
     */
    private function check(string $type):bool
    {
      if($this->isAtEnd()) return false;
      return $this->peek()->type === $type;
    }

    /**
     * Advance to next token and return previous token
     * @return Token The previous token
     */
    private function advance():Token
    {
      if(! $this->isAtEnd()) $this->current++;
      return $this->previous();
    }

    /**
     * Check if we've reached end of input
     * @return bool True if at end of input
     */
    private function isAtEnd():bool
    {
      return $this->peek()->type == TokenType::EOF;
    }

    /**
     * Get current token without consuming it
     * @return Token The current token
     */
    private function peek():Token
    {
      return $this->tokens[$this->current];
    }

    /**
     * Get previous token
     * @return Token The previous token
     */
    private function previous():Token
    {
      return $this->tokens[$this->current -1];
    }

}

class ParzerError extends \RuntimeException{}