<?php

namespace Phlox;

use PDO;
use Phlox\DS\Map;
use Phlox\Expr\Assign;
use Phlox\Expr\Visitor as ExpressionVisitor;
use Phlox\Stmt\Visitor as StatementVisitor;
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
use Phlox\Return_ as PhloxReturn_;
use Phlox\Stmt\AClass;
use Phlox\Stmt\Block;
use Phlox\Stmt\Expression;
use Phlox\Stmt\Function_;
use Phlox\Stmt\If_;
use Phlox\Stmt\Printr;
use Phlox\Stmt\Return_;
use Phlox\Stmt\ReturnR;
use Phlox\Stmt\Stmt;
use Phlox\Stmt\Var_;
use Phlox\Stmt\While_;
use PhpCsFixer\ToolInfo;

class Interpreter implements ExpressionVisitor, StatementVisitor{

    private Environment $environment;
    public Environment $globals;
    private Map $locals;
    public $test = 88487383;

    private function getInterpreterLocals():Map
    {
        if(!isset($this->locals)){
            $this->locals = new Map();
        }

        return $this->locals;
    }

    private function getEnvironment():Environment
    {
        if (! isset($this->environment)){
            $this->globals = new Environment();
            $this->environment = $this->globals;
        }

        $this->globals->define("clock", new class {
            public function arity(){ return 0;}

            public function call(Interpreter $interpreter, array $arguments){
                return (double) time();
            }

            public function __toString()
            {
                return "<native fn>";
            }
        });

        return $this->environment;
    }

    public function visitAssignExpr(Assign $expr){
        $value = $this->evaluate($expr->value);
        // $this->getEnvironment()->assign($expr->name, $value);
        $distance = $this->getInterpreterLocals()->get($expr);
        if($distance != null){
            $this->getEnvironment()->assignAt($distance, $expr->name, $value);
        } else {
            $this->globals->assign($expr->name, $value);
        }
    }

    public function visitBinaryExpr(Binary $expr){
        $left = $this->evaluate($expr->left);
        $right = $this->evaluate($expr->right);

        switch($expr->operator->type){
            case TokenType::GREATER:
                $this->_checkNumberOperand($expr->operator, $left, $right);
                return (double) $left > (double) $right;
            case TokenType::GREATER_EQUAL:
                $this->_checkNumberOperand($expr->operator, $left, $right);
                return (double) $left >= (double) $right;
            case TokenType::LESS:
                $this->_checkNumberOperand($expr->operator, $left, $right);
                return (double) $left < (double) $right;
            case TokenType::LESS_EQUAL:
                $this->_checkNumberOperand($expr->operator, $left, $right);
                return (double) $left <= (double) $right;
            case TokenType::MINUS:
                $this->_checkNumberOperand($expr->operator, $left, $right);
                return (double) $left - (double) $right;
            case TokenType::PLUS:
                if(gettype($left) === 'double' && gettype($right) === 'double') {
                    return (double) $left + (double) $right;
                }

                if(gettype($left) === 'string' && gettype($right) === 'string'){
                    return (string) $left . (string) $right;
                }

                throw new RuntimeError($expr->operator, "Operands must be two numbers or two strings.");
            break;
            case TokenType::SLASH:
                $this->_checkNumberOperand($expr->operator, $left, $right);
                return (double) $left / (double) $right;
            case TokenType::STAR:
                $this->_checkNumberOperand($expr->operator, $left, $right);
                return (double) $left * (double) $right;
            case TokenType::BANG_EQUAL: return ! $this->isEqual($left, $right);
            case TokenType::EQUAL_EQUAL: return $this->isEqual($left, $right);
        }

        return null;
    }

    public function visitCallExpr(Call $expr){
        $callee = $this->evaluate($expr->callee);

        $arguments = [];
        foreach($expr->arguments as $argument){
            $arguments [] = $this->evaluate($argument);
        }

        if (!($callee instanceof LoxCallable)){
            throw new RuntimeError($expr->paren, "Can only call functions and classes.");
        }

        $function =  $callee; // (LoxCallable)
        if (count($arguments) != $function->arity()){
            throw new RuntimeError($expr->paren, "Expected ".$function->arity()." arguments but got ".count($arguments).".");
        }

        return $function->call($this, $arguments);
    }

    public function visitGetExpr(Get $expr){
        $object = $this->evaluate($expr->object);

        if($object instanceof LoxInstance){
            return $object->get($expr->name);
        }

        throw new RuntimeError($expr->name, "Only instances have properties");
    }

    public function visitBlockStmt(Block $stmt)
    {
      $this->executeBlock($stmt->statements, new Environment($this->getEnvironment()));
      return null;
    }

    public function visitClassStmt(AClass $stmt)
    {
        $this->getEnvironment()->define($stmt->name->lexeme, null);

        $methods = new Map();
        foreach($stmt->methods as $method){
            $function = new LoxFunction($method, $this->getEnvironment());
            $methods->put($method->name->lexeme, $function);
        }

        $klass = new LoxClass($stmt->name->lexeme, $methods);
        $this->getEnvironment()->assign($stmt->name, $klass);
    }

    public function visitGroupingExpr(Grouping $expr){
        return $this->evaluate($expr->expression);
    }

    private function evaluate(Expr $expr) {
        return $expr->accept($this);
    }

    private function execute(Stmt $stmt)
    {
        $stmt->accept($this);
    }

    function resolve(Expr $expr, int $depth)
    {
        $this->getInterpreterLocals()->put($expr, $depth);
    }

    public function executeBlock(array $statements, Environment $environment) {
        $previous = $this->getEnvironment();

        try{
            $this->environment =  $environment;

            foreach ($statements as $statement){
                $this->execute($statement);
            }
        } finally {
            $this->environment =  $previous; 
        }
    }

    public function visitLiteralExpr(Literal $expr){
        return $expr->value;
    }

    public function visitLogicalExpr(Logical $expr)
    {
        $left = $this->evaluate($expr->left);

        if($expr->operator->type === TokenType::OR){
            if ($this->isTruthy($left)) return $left;
        } else {
            if (! $this->isTruthy($left)) return $left;
        }

        return $this->evaluate($expr->right);
    }

    public function visitUnaryExpr(Unary $expr) {
        $right = $this->evaluate($expr->right);
    
        switch ($expr->operator->type) {
            
            case TokenType::BANG:
                return ! $this->isTruthy($right);
            case TokenType::MINUS:
                $this->checkNumberOperand($expr->operator, $right);
                return -(double) $right;

        }
    
        // Unreachable.
        return null;
    }

    public function visitVariableExpr(Variable $expr)
    {
        // return $this->getEnvironment()->get($expr->name);
        return $this->lookUpVariable($expr->name, $expr);    
    }

    private function lookUpVariable(Token $name, Expr $expr)
    {
        $distance = $this->getInterpreterLocals()->get($expr);
        if($distance !== null) {
            return $this->getEnvironment()->getAt($distance, $name->lexeme); //checj=
        } else { 
            return $this->globals->get($name);
        }
    }

    private function checkNumberOperand(Token $operator, $operand)
    {
        if(gettype($operand) === 'double') return;
    }

    private function _checkNumberOperand(Token $operator, $left, $right)
    {
        if (gettype($left) === 'double' && gettype($right) === 'double') return;

        throw new RuntimeError($operator, "Operands must be numbers.");
    }

    private function isTruthy($object):bool
    {
        if ($object === null) return false;
        if (gettype($object) === 'boolean') return (bool) $object;
        return true;
    }

    private function isEqual($a, $b)
    {
        if($a === null && $b === null) return true;
        if($a === null) return false;

        return $a === $b;
    }

    private function stringify($object)
    {
        if($object === null) return "nil";

        if(gettype($object) === 'double'){
            $text = strval($object);
            if(substr($text,-2) === '.0'){
                $text = substr($text,0, strlen($text)-2);
            };

            return $text;
        }

        return strval($object);
    }

    public function visitExpressionStmt(Expression $stmt)
    {
        $this->evaluate($stmt->expression);
        return null;
    }

    public function visitFunctionStmt(Function_ $stmt)
    {
        $function = new LoxFunction($stmt, $this->getEnvironment());
        $this->getEnvironment()->define($stmt->name->lexeme, $function);
        return null;
    }

    public function visitIfStmt(If_ $statement)
    {
        if ($this->isTruthy($this->evaluate($statement->condition))){
            $this->execute($statement->thenBranch);
        } else if ($statement->elseBranch !== null) {
            $this->execute($statement->elseBranch);
        } 

        return null;
    }

    public function visitPrintStmt(Printr $stmt)
    {
        $value = $this->evaluate($stmt->expression);
        echo $this->stringify($value);
        echo "\n";
        return null;
    }

    public function visitReturnStmt(ReturnR $stmt)
    {
        $value = null;
        if($stmt->value !== null) $value = $this->evaluate($stmt->value);

        throw new PhloxReturn_($value);
    }

    public function visitVarStmt(Var_ $stmt)
    {
        $value = null;
        if($stmt->intializer != null){
            $value = $this->evaluate($stmt->intializer);
        }

        $this->getEnvironment()->define($stmt->name->lexeme, $value);
        return null;
    }

    public function visitWhileStmt(While_ $stmt)
    {
        while ($this->isTruthy($this->evaluate($stmt->condition)))
        {
            $this->execute($stmt->body);
        }

        return null;
    }

    // public function visitLogicalExpr(Logical $expr){}
    public function visitSetExpr(Set $expr){
        $object = $this->evaluate($expr->object);

        if(!($object instanceof LoxInstance)){
            throw new RuntimeError($expr->name, "Only instances have fields.");
        }

        $value = $this->evaluate($expr->value);
        ($object)->set($expr->name, $value);
        return $value;
    }
    public function visitSuperExpr(Super $expr){}
    public function visitThisExpr(This $expr){}
    // public function visitUnaryExpr(Unary $expr){}


    public function interpret(array $statements)
    {
        try {
            foreach($statements as $statement){
                $this->execute($statement);
            }
            // $value = $this->evaluate($expression);
            // print_r($this->stringify($value));
            // print("\n");
        } catch (RuntimeError $error) {
            Phlox::runtimeError($error);
        }
    }
}