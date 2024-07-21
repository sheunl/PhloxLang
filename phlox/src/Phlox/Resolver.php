<?php 
namespace Phlox;

use Phlox\DS\Map;
use Phlox\Expr\Visitor as ExprVisitor;
use Phlox\Stmt\Visitor as StmtVisitor;
use Phlox\DS\Stack;
use Phlox\Expr\Assign;
use Phlox\Expr\Binary;
use Phlox\Expr\Call;
use Phlox\Stmt\Block;
use Phlox\Stmt\Stmt;
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
use Phlox\Stmt\AClass;
use Phlox\Stmt\Expression;
use Phlox\Stmt\Function_;
use Phlox\Stmt\If_;
use Phlox\Stmt\Printr;
use Phlox\Stmt\Var_;
use Phlox\Stmt\ReturnR;
use Phlox\Stmt\While_;

class Resolver implements ExprVisitor, StmtVisitor
{
    private array $scopes = [];
    private Interpreter $interpreter;

    private $currentFunction = FunctionType::NONE;
    private $currentClass = ClassType::NONE;

    public function __construct(Interpreter $interpreter = null){
        if($interpreter === null){
            $this->interpreter = new Interpreter();
        } else {
            $this->interpreter = $interpreter;
        }
           
    }

    public function visitBlockStmt(Block $statement)
    {
        $this->beginScope();
        $this->resolve($statement->statements);
        $this->endScope();

        return null;
    }

    public function visitClassStmt(AClass $stmt)
    {
        $enclosingClass = $this->currentClass;
        $this->currentClass = ClassType::ACLASS;

        $this->declare($stmt->name);
        $this->define($stmt->name);

        if ($stmt->superclass != null && $stmt->name->lexeme === $stmt->superclass->name->lexeme){
            Phlox::error_($stmt->superclass->name, "A class can't inherit from itself.");
        }

        if ($stmt->superclass != null){
            $this->currentClass = ClassType::SUBCLASS;
            $this->resolveExpr($stmt->superclass);
        }

        if ($stmt->superclass != null){
            $this->beginScope();
            $this->scopes[count($this->scopes) - 1]->put("super", true);
        }

        $this->beginScope();
        $this->scopes[count($this->scopes) - 1]->put("this",true);

        foreach($stmt->methods as $method){
            $declaration = FunctionType::METHOD;
            if ($method->name->lexeme === "init"){
                $declaration = FunctionType::INITIALIZER;
            }

            $this->resolveFunction($method, $declaration);
        }

        $this->endScope();

        if ($stmt->superclass != null) $this->endScope();

        $this->currentClass = $enclosingClass;
        return null;
    }

    public function visitExpressionStmt(Expression $stmt)
    {
        $this->resolveExpr($stmt->expression);
        return null;
    }

    public function visitIfStmt(If_ $stmt)
    {
        $this->resolveExpr($stmt->condition);
        $this->resolveStmt($stmt->thenBranch);

        if ($stmt->elseBranch != null) $this->resolveStmt($stmt->elseBranch);
        return null;
    }

    public function visitPrintStmt(Printr $stmt)
    {
        $this->resolveExpr($stmt->expression);
        return null;
    }

    public function visitReturnStmt(ReturnR $stmt)
    {
        if ($this->currentFunction === FunctionType::NONE){
            Phlox::error_($stmt->keyword, "Can't return from top-level code.");
        }

        if ($stmt->value != null){

            if($this->currentFunction === FunctionType::INITIALIZER){
                Phlox::error_($stmt->keyword, "Can't return a value from an initializer.");
            }

            $this->resolveExpr($stmt->value);
        }
    }

    public function visitWhileStmt(While_ $stmt)
    {
        $this->resolveExpr($stmt->condition);
        $this->resolveStmt($stmt->body);

        return null;
    }

    public function visitVarStmt(Var_ $stmt)
    {
        $this->declare($stmt->name);
        if($stmt->intializer != null){
            $this->resolveExpr($stmt->intializer);
        }

        $this->define($stmt->name);
        return null;
    }

    public function visitAssignExpr(Assign $expr)
    {
        $this->resolveExpr($expr->value);
        $this->resolveLocal($expr, $expr->name);

        return null;
    }

    public function visitBinaryExpr(Binary $expr)
    {
        $this->resolveExpr($expr->left);
        $this->resolveExpr($expr->right);

        return null;
    }

    public function visitCallExpr(Call $expr)
    {
        $this->resolveExpr($expr->callee);

        foreach($expr->arguments as $argument){
            $this->resolveExpr($argument);
        }

        return null;
    }

    public function visitGetExpr(Get $expr)
    {
        $this->resolveExpr($expr->object);
        return null;
    }

    public function visitGroupingExpr(Grouping $expr)
    {
        $this->resolveExpr($expr->expression);
        return null;
    }

    public function visitLiteralExpr(Literal $expr)
    {
        return null;
    }

    public function visitLogicalExpr(Logical $expr)
    {
        $this->resolveExpr($expr->left);
        $this->resolveExpr($expr->right);

        return null;
    }


    public function visitSetExpr(Set $expr)
    {
        $this->resolveExpr($expr->value);
        $this->resolveExpr($expr->object);
        return null;
    }

    public function visitSuperExpr(Super $expr)
    {
        if($this->currentClass === ClassType::NONE){
            Phlox::error_($expr->keyword, "Can't use 'super' outside of a class.");
        } else if ($this->currentClass != ClassType::SUBCLASS) {
            Phlox::error_($expr->keyword, "Can't use 'super' in a class with no superclass.");
        }

        $this->resolveLocal($expr, $expr->keyword);
        return null;
    }

    public function visitThisExpr(This $expr)
    {
        if ($this->currentClass === ClassType::NONE){
            Phlox::error_($expr->keyword, "Can't use 'this' outside of a class.");
            return null;
        }

        $this->resolveLocal($expr, $expr->keyword);
        
        return null;
    }

    public function visitUnaryExpr(Unary $expr)
    {
        $this->resolveExpr($expr->right);
        return null;
    }

    public function visitFunctionStmt(Function_ $stmt)
    {
        $this->declare($stmt->name);
        $this->define($stmt->name);

        $this->resolveFunction($stmt, FunctionType::FUNCTION);
        return null;
    }

    public function visitVariableExpr(Variable $expr)
    {
        if(count($this->scopes) && null !== $this->scopes[count($this->scopes) - 1]->get($expr->name->lexeme) && $this->scopes[count($this->scopes) - 1]->get($expr->name->lexeme) === false){
            Phlox::error_($expr->name, "Can't read local variable in its own initializer.");
        }

        $this->resolveLocal($expr, $expr->name);
        return null;
    }

    function resolve(array $statements)
    {
        foreach($statements as $statement){
            $this->resolveStmt($statement);
        }
    }

    private function resolveStmt(Stmt $stmt)
    {
        // Resolve this later ;-)
        $stmt->accept($this);
    }

    private function resolveExpr(Expr $expr)
    {
        $expr->accept($this);
    }

    private function resolveFunction(Function_ $function, FunctionType | string $type)
    {        

        $enclosingFunction  = $this->currentFunction;
        $this->currentFunction = $type;

        $this->beginScope();

        foreach($function->params as $param)
        {
            $this->declare($param);
            $this->define($param);
        }   

        $this->resolve($function->body);
        $this->endScope();
        $this->currentFunction = $enclosingFunction;
     
    }

    private function beginScope()
    {
        // $this->scopes[] = new Map(); //Old
        // array_unshift($this->scopes, new Map());
        array_push($this->scopes, new Map());
    }

    private function endScope()
    {
        // array_shift($this->scopes);
        array_pop($this->scopes);
    }

    private function declare(Token $name)
    {
        if (count($this->scopes) === 0) return;

        $scope = $this->scopes[count($this->scopes) - 1];

        if($scope->hasKey($name->lexeme)){
            Phlox::error_($name, "Already a variable with this name in this scope.");
        }

        // $scope[$name->lexeme] = false; //Old

        $scope->put($name->lexeme, false); 
    
    }

    private function define(Token $name)
    {
        if(count($this->scopes) === 0) return;

        $this->scopes[count($this->scopes) - 1]->put($name->lexeme ,true);
    }

    private function resolveLocal(Expr $expr, Token $name)
    {
        for($i = (count($this->scopes) - 1 ); $i >= 0; $i--){
            if($this->scopes[$i]->hasKey($name->lexeme)){
                $this->interpreter->resolve($expr, count($this->scopes) - 1 - $i);
                return;
            }
        }
    }







} 