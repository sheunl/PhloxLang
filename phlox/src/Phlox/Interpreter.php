<?php

namespace Phlox;

use Phlox\Expr\Assign;
use Phlox\Expr\Visitor;
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
use PhpCsFixer\ToolInfo;

class Interpreter implements Visitor{
    public function visitAssignExpr(Assign $expr){

    }

    public function visitBinaryExpr(Binary $expr){
        $left = $this->evaluate($expr->left);
        $right = $this->evaluate($expr->right);

        switch($expr->operator->type){
            case TokenType::GREATER:
                $this->checkNumberOperand($expr->operator, $left, $right);
                return (double) $left > (double) $right;
            case TokenType::GREATER_EQUAL:
                $this->checkNumberOperand($expr->operator, $left, $right);
                return (double) $left >= (double) $right;
            case TokenType::LESS:
                $this->checkNumberOperand($expr->operator, $left, $right);
                return (double) $left < (double) $right;
            case TokenType::LESS_EQUAL:
                $this->checkNumberOperand($expr->operator, $left, $right);
                return (double) $left <= (double) $right;
            case TokenType::MINUS:
                $this->checkNumberOperand($expr->operator, $left, $right);
                return (double) $left - (double) $right;
            case TokenType::PLUS:
                if(gettype($left) === 'double' && gettype($right) === 'double') {
                    return (double) $left + (double) $right;
                }

                if(gettype($left) === 'string' && gettype($right) === 'string'){
                    return (string) $left + (string) $right;
                }

                throw new RuntimeError($expr->operator, "Operands must be two numbers or two strings.");
            break;
            case TokenType::SLASH:
                $this->checkNumberOperand($expr->operator, $left, $right);
                return (double) $left / (double) $right;
            case TokenType::STAR:
                $this->checkNumberOperand($expr->operator, $left, $right);
                return (double) $left * (double) $right;
            case TokenType::BANG_EQUAL: return ! $this->isEqual($left,$right);
            case TokenType::EQUAL_EQUAL: return $this->isEqual($left, $right);
        }

        return null;
    }

    public function visitCallExpr(Call $expr){
        
    }

    public function visitGetExpr(Get $expr){}

    public function visitGroupingExpr(Grouping $expr){
        return $this->evaluate($expr->expression);
    }

    private function evaluate(Expr $expr) {
        return $expr->accept($this);
    }

    public function visitLiteralExpr(Literal $expr){
        return $expr->value;
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
        if (gettype($object) === 'boolean') $object;
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

    public function visitLogicalExpr(Logical $expr){}
    public function visitSetExpr(Set $expr){}
    public function visitSuperExpr(Super $expr){}
    public function visitThisExpr(This $expr){}
    // public function visitUnaryExpr(Unary $expr){}
    public function visitVariableExpr(Variable $expr){}

    public function interpret(Expr $expression)
    {
        try {
            $value = $this->evaluate($expression);
            print_r($value);
        } catch (RuntimeError $error) {
            Phlox::runtimeError($error);
        }
    }
}