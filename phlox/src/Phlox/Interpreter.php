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

/**
 * Main interpreter class that executes Lox code
 * Implements both expression and statement visitors to traverse and evaluate the AST
 */
class Interpreter implements ExpressionVisitor, StatementVisitor{

    /**
     * Current environment for variable scoping
     * @var Environment
     */
    private Environment $environment;

    /**
     * Global environment containing built-in functions
     * @var Environment  
     */
    public Environment $globals;

    /**
     * Map of resolved variable scopes
     * @var Map
     */
    private Map $locals;

    /**
     * Initialize interpreter with global environment
     */
    public function __construct()
    {
        $this->globals = new Environment();
        $this->globals->define("clock", new class implements LoxCallable{
            public function arity(): int{ return 0;}

            public function call(Interpreter $interpreter, array $arguments){
                return (double) time();
            }

            public function __toString()
            {
                return "<native fn>";
            }
        });
    }

    /**
     * Get the locals map, initializing if needed
     * @return Map The locals map
     */
    private function getInterpreterLocals():Map
    {
        if(!isset($this->locals)){
            $this->locals = new Map();
        }

        return $this->locals;
    }

    /**
     * Get the current environment, defaulting to globals if not set
     * @return Environment The current environment
     */
    private function getEnvironment():Environment
    {
        if (! isset($this->environment)){
            $this->environment = $this->globals;
        }

        return $this->environment;
    }

    /**
     * Visit and evaluate an assignment expression
     * @param Assign $expr The assignment expression
     * @return mixed The assigned value
     */
    public function visitAssignExpr(Assign $expr){
        $value = $this->evaluate($expr->value);
        $distance = $this->getInterpreterLocals()->get($expr);
        if($distance !== null){
            $this->getEnvironment()->assignAt($distance, $expr->name, $value);
        } else {
            $this->globals->assign($expr->name, $value);
        }
    }

    /**
     * Visit and evaluate a binary expression
     * @param Binary $expr The binary expression
     * @return mixed The result of the operation
     */
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

    /**
     * Visit and evaluate a function call expression
     * @param Call $expr The call expression
     * @return mixed The result of the function call
     */
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

    /**
     * Visit and evaluate a property access expression
     * @param Get $expr The get expression
     * @return mixed The value of the property
     */
    public function visitGetExpr(Get $expr){
        $object = $this->evaluate($expr->object);

        if($object instanceof LoxInstance){
            return $object->get($expr->name);
        }

        throw new RuntimeError($expr->name, "Only instances have properties");
    }

    /**
     * Visit and execute a block statement
     * @param Block $stmt The block statement
     * @return null
     */
    public function visitBlockStmt(Block $stmt)
    {
      $this->executeBlock($stmt->statements, new Environment($this->getEnvironment()));
      return null;
    }

    /**
     * Visit and execute a class declaration
     * @param AClass $stmt The class declaration
     * @return null
     */
    public function visitClassStmt(AClass $stmt)
    {
        $superclass = null;

        if($stmt->superclass != null){
            $superclass = $this->evaluate($stmt->superclass);
            if(!($superclass instanceof LoxClass)) {
                throw new RuntimeError($stmt->superclass->name, "Superclass must be a class.");
            }
        }

        $this->getEnvironment()->define($stmt->name->lexeme, null);

        if ($stmt->superclass != null){
            $this->environment = new Environment($this->getEnvironment());
            $this->environment->define("super", $stmt->superclass);
        }

        $methods = new Map();
        foreach($stmt->methods as $method){
            $function = new LoxFunction($method, $this->getEnvironment(), $method->name->lexeme === "init");
            $methods->put($method->name->lexeme, $function);
        }

        $klass = new LoxClass($stmt->name->lexeme, $superclass, $methods);

        if ($superclass != null){
            $this->environment = $this->getEnvironment()->enclosing;        
        }

        $this->getEnvironment()->assign($stmt->name, $klass);
    }

    /**
     * Visit and evaluate a grouping expression
     * @param Grouping $expr The grouping expression
     * @return mixed The evaluated result
     */
    public function visitGroupingExpr(Grouping $expr){
        return $this->evaluate($expr->expression);
    }

    /**
     * Evaluate an expression by accepting this visitor
     * @param Expr $expr The expression to evaluate
     * @return mixed The evaluated result
     */
    private function evaluate(Expr $expr) {
        return $expr->accept($this);
    }

    /**
     * Execute a statement by accepting this visitor
     * @param Stmt $stmt The statement to execute
     */
    private function execute(Stmt $stmt)
    {
        $stmt->accept($this);
    }

    /**
     * Resolve a variable reference to its scope depth
     * @param Expr $expr The variable expression
     * @param int $depth The scope depth
     */
    function resolve(Expr $expr, int $depth)
    {
        $this->getInterpreterLocals()->put($expr, $depth);
    }

    /**
     * Execute a block of statements in a new environment
     * @param array $statements The statements to execute
     * @param Environment $environment The new environment
     */
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

    /**
     * Visit and evaluate a literal expression
     * @param Literal $expr The literal expression
     * @return mixed The literal value
     */
    public function visitLiteralExpr(Literal $expr){
        return $expr->value;
    }

    /**
     * Visit and evaluate a logical expression
     * @param Logical $expr The logical expression
     * @return mixed The result of the logical operation
     */
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

    /**
     * Visit and evaluate a unary expression
     * @param Unary $expr The unary expression
     * @return mixed The result of the unary operation
     */
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

    /**
     * Visit and evaluate a variable expression
     * @param Variable $expr The variable expression
     * @return mixed The variable's value
     */
    public function visitVariableExpr(Variable $expr)
    {
        return $this->lookUpVariable($expr->name, $expr);    
    }

    /**
     * Look up a variable's value in the appropriate scope
     * @param Token $name The variable name token
     * @param Expr $expr The variable expression
     * @return mixed The variable's value
     */
    private function lookUpVariable(Token $name, Expr $expr)
    {
        $distance = $this->getInterpreterLocals()->get($expr);
        if($distance !== null) {
            return $this->getEnvironment()->getAt($distance, $name->lexeme); //checj=
        } else { 
            return $this->globals->get($name);
        }
    }

    /**
     * Check if an operand is a number
     * @param Token $operator The operator token
     * @param mixed $operand The operand to check
     */
    private function checkNumberOperand(Token $operator, $operand)
    {
        if(gettype($operand) === 'double') return;
    }

    /**
     * Check if both operands are numbers
     * @param Token $operator The operator token
     * @param mixed $left The left operand
     * @param mixed $right The right operand
     */
    private function _checkNumberOperand(Token $operator, $left, $right)
    {
        if (gettype($left) === 'double' && gettype($right) === 'double') return;

        throw new RuntimeError($operator, "Operands must be numbers.");
    }

    /**
     * Determine if a value is truthy in Lox
     * @param mixed $object The value to check
     * @return bool True if the value is truthy
     */
    private function isTruthy($object):bool
    {
        if ($object === null) return false;
        if (gettype($object) === 'boolean') return (bool) $object;
        return true;
    }

    /**
     * Check if two values are equal
     * @param mixed $a First value
     * @param mixed $b Second value
     * @return bool True if values are equal
     */
    private function isEqual($a, $b)
    {
        if($a === null && $b === null) return true;
        if($a === null) return false;

        return $a === $b;
    }

    /**
     * Convert a value to its string representation
     * @param mixed $object The value to stringify
     * @return string The string representation
     */
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

    /**
     * Visit and execute an expression statement
     * @param Expression $stmt The expression statement
     * @return null
     */
    public function visitExpressionStmt(Expression $stmt)
    {
        $this->evaluate($stmt->expression);
        return null;
    }

    /**
     * Visit and execute a function declaration
     * @param Function_ $stmt The function declaration
     * @return null
     */
    public function visitFunctionStmt(Function_ $stmt)
    {
        $function = new LoxFunction($stmt, $this->getEnvironment(), false);
        $this->getEnvironment()->define($stmt->name->lexeme, $function);
        return null;
    }

    /**
     * Visit and execute an if statement
     * @param If_ $statement The if statement
     * @return null
     */
    public function visitIfStmt(If_ $statement)
    {
        if ($this->isTruthy($this->evaluate($statement->condition))){
            $this->execute($statement->thenBranch);
        } else if ($statement->elseBranch !== null) {
            $this->execute($statement->elseBranch);
        } 

        return null;
    }

    /**
     * Visit and execute a print statement
     * @param Printr $stmt The print statement
     * @return null
     */
    public function visitPrintStmt(Printr $stmt)
    {
        $value = $this->evaluate($stmt->expression);
        echo $this->stringify($value);
        echo "\n";
        return null;
    }

    /**
     * Visit and execute a return statement
     * @param ReturnR $stmt The return statement
     * @throws PhloxReturn_ The return value wrapped in an exception
     */
    public function visitReturnStmt(ReturnR $stmt)
    {
        $value = null;
        if($stmt->value !== null) $value = $this->evaluate($stmt->value);

        throw new PhloxReturn_($value);
    }

    /**
     * Visit and execute a variable declaration
     * @param Var_ $stmt The variable declaration
     * @return null
     */
    public function visitVarStmt(Var_ $stmt)
    {
        $value = null;
        if($stmt->intializer != null){
            $value = $this->evaluate($stmt->intializer);
        }

        $this->getEnvironment()->define($stmt->name->lexeme, $value);
        return null;
    }

    /**
     * Visit and execute a while statement
     * @param While_ $stmt The while statement
     * @return null
     */
    public function visitWhileStmt(While_ $stmt)
    {
        while ($this->isTruthy($this->evaluate($stmt->condition)))
        {
            $this->execute($stmt->body);
        }

        return null;
    }

    /**
     * Visit and evaluate a property assignment
     * @param Set $expr The set expression
     * @return mixed The assigned value
     */
    public function visitSetExpr(Set $expr){
        $object = $this->evaluate($expr->object);

        if(!($object instanceof LoxInstance)){
            throw new RuntimeError($expr->name, "Only instances have fields.");
        }

        $value = $this->evaluate($expr->value);
        ($object)->set($expr->name, $value);
        return $value;
    }

    /**
     * Visit and evaluate a super expression
     * @param Super $expr The super expression
     * @return mixed The superclass method
     */
    public function visitSuperExpr(Super $expr)
    {
        $distance = $this->locals->get($expr);

        $superclass = $this->getEnvironment()->getAt($distance, "super");

        $object = $this->getEnvironment()->getAt($distance - 1, "this");

        if ($superclass instanceof LoxClass)
            $method = $superclass->findMethod($expr->method->lexeme);

        if ($method === null){
            throw new RuntimeError($expr->method, "Undefined property '". $expr->method->lexeme."'.");
        }

        return $method->bind($object);
    }

    /**
     * Visit and evaluate a this expression
     * @param This $expr The this expression
     * @return mixed The this instance
     */
    public function visitThisExpr(This $expr){
        return $this->lookUpVariable($expr->keyword, $expr);
    }

    /**
     * Interpret a sequence of statements
     * @param array $statements The statements to interpret
     */
    public function interpret(array $statements)
    {
        try {
            foreach($statements as $statement){
                $this->execute($statement);
            }
            
        } catch (RuntimeError $error) {
            Phlox::runtimeError($error);
        }
    }
}