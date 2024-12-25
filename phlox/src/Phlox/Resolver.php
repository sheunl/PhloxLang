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

/**
 * Resolver performs variable resolution and scope analysis
 * Implements both expression and statement visitors
 */
class Resolver implements ExprVisitor, StmtVisitor
{
    /**
     * Stack of scopes for variable resolution
     * @var array
     */
    private array $scopes = [];

    /**
     * Reference to interpreter instance
     * @var Interpreter
     */
    private Interpreter $interpreter;

    /**
     * Current function type being resolved
     * @var string
     */
    private $currentFunction = FunctionType::NONE;

    /**
     * Current class type being resolved
     * @var string
     */
    private $currentClass = ClassType::NONE;

    /**
     * Initialize resolver with optional interpreter instance
     * @param Interpreter|null $interpreter The interpreter instance
     */
    public function __construct(Interpreter $interpreter = null){
        if($interpreter === null){
            $this->interpreter = new Interpreter();
        } else {
            $this->interpreter = $interpreter;
        }
    }

    /**
     * Visit a block statement node
     * @param Block $statement The block statement to visit
     * @return null
     */
    public function visitBlockStmt(Block $statement)
    {
        $this->beginScope();
        $this->resolve($statement->statements);
        $this->endScope();

        return null;
    }

    /**
     * Visit a class declaration node
     * @param AClass $stmt The class declaration to visit
     * @return null
     */
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

    /**
     * Visit an expression statement node
     * @param Expression $stmt The expression statement to visit
     * @return null
     */
    public function visitExpressionStmt(Expression $stmt)
    {
        $this->resolveExpr($stmt->expression);
        return null;
    }

    /**
     * Visit an if statement node
     * @param If_ $stmt The if statement to visit
     * @return null
     */
    public function visitIfStmt(If_ $stmt)
    {
        $this->resolveExpr($stmt->condition);
        $this->resolveStmt($stmt->thenBranch);

        if ($stmt->elseBranch != null) $this->resolveStmt($stmt->elseBranch);
        return null;
    }

    /**
     * Visit a print statement node
     * @param Printr $stmt The print statement to visit
     * @return null
     */
    public function visitPrintStmt(Printr $stmt)
    {
        $this->resolveExpr($stmt->expression);
        return null;
    }

    /**
     * Visit a return statement node
     * @param ReturnR $stmt The return statement to visit
     * @return void
     */
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

    /**
     * Visit a while statement node
     * @param While_ $stmt The while statement to visit
     * @return null
     */
    public function visitWhileStmt(While_ $stmt)
    {
        $this->resolveExpr($stmt->condition);
        $this->resolveStmt($stmt->body);

        return null;
    }

    /**
     * Visit a variable declaration node
     * @param Var_ $stmt The variable declaration to visit
     * @return null
     */
    public function visitVarStmt(Var_ $stmt)
    {
        $this->declare($stmt->name);
        if($stmt->intializer != null){
            $this->resolveExpr($stmt->intializer);
        }

        $this->define($stmt->name);
        return null;
    }

    /**
     * Visit an assignment expression node
     * @param Assign $expr The assignment expression to visit
     * @return null
     */
    public function visitAssignExpr(Assign $expr)
    {
        $this->resolveExpr($expr->value);
        $this->resolveLocal($expr, $expr->name);

        return null;
    }

    /**
     * Visit a binary expression node
     * @param Binary $expr The binary expression to visit
     * @return null
     */
    public function visitBinaryExpr(Binary $expr)
    {
        $this->resolveExpr($expr->left);
        $this->resolveExpr($expr->right);

        return null;
    }

    /**
     * Visit a function call expression node
     * @param Call $expr The function call expression to visit
     * @return null
     */
    public function visitCallExpr(Call $expr)
    {
        $this->resolveExpr($expr->callee);

        foreach($expr->arguments as $argument){
            $this->resolveExpr($argument);
        }

        return null;
    }

    /**
     * Visit a property access expression node
     * @param Get $expr The property access expression to visit
     * @return null
     */
    public function visitGetExpr(Get $expr)
    {
        $this->resolveExpr($expr->object);
        return null;
    }

    /**
     * Visit a grouping expression node
     * @param Grouping $expr The grouping expression to visit
     * @return null
     */
    public function visitGroupingExpr(Grouping $expr)
    {
        $this->resolveExpr($expr->expression);
        return null;
    }

    /**
     * Visit a literal value expression node
     * @param Literal $expr The literal expression to visit
     * @return null
     */
    public function visitLiteralExpr(Literal $expr)
    {
        return null;
    }

    /**
     * Visit a logical operation expression node
     * @param Logical $expr The logical expression to visit
     * @return null
     */
    public function visitLogicalExpr(Logical $expr)
    {
        $this->resolveExpr($expr->left);
        $this->resolveExpr($expr->right);

        return null;
    }

    /**
     * Visit a property assignment expression node
     * @param Set $expr The property assignment expression to visit
     * @return null
     */
    public function visitSetExpr(Set $expr)
    {
        $this->resolveExpr($expr->value);
        $this->resolveExpr($expr->object);
        return null;
    }

    /**
     * Visit a super expression node
     * @param Super $expr The super expression to visit
     * @return null
     */
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

    /**
     * Visit a this expression node
     * @param This $expr The this expression to visit
     * @return null
     */
    public function visitThisExpr(This $expr)
    {
        if ($this->currentClass === ClassType::NONE){
            Phlox::error_($expr->keyword, "Can't use 'this' outside of a class.");
            return null;
        }

        $this->resolveLocal($expr, $expr->keyword);
        
        return null;
    }

    /**
     * Visit a unary expression node
     * @param Unary $expr The unary expression to visit
     * @return null
     */
    public function visitUnaryExpr(Unary $expr)
    {
        $this->resolveExpr($expr->right);
        return null;
    }

    /**
     * Visit a function declaration node
     * @param Function_ $stmt The function declaration to visit
     * @return null
     */
    public function visitFunctionStmt(Function_ $stmt)
    {
        $this->declare($stmt->name);
        $this->define($stmt->name);

        $this->resolveFunction($stmt, FunctionType::FUNCTION);
        return null;
    }

    /**
     * Visit a variable expression node
     * @param Variable $expr The variable expression to visit
     * @return null
     */
    public function visitVariableExpr(Variable $expr)
    {
        if(count($this->scopes) && null !== $this->scopes[count($this->scopes) - 1]->get($expr->name->lexeme) && $this->scopes[count($this->scopes) - 1]->get($expr->name->lexeme) === false){
            Phlox::error_($expr->name, "Can't read local variable in its own initializer.");
        }

        $this->resolveLocal($expr, $expr->name);
        return null;
    }

    /**
     * Resolve an array of statements
     * @param array $statements The statements to resolve
     */
    function resolve(array $statements)
    {
        foreach($statements as $statement){
            $this->resolveStmt($statement);
        }
    }

    /**
     * Resolve a single statement
     * @param Stmt $stmt The statement to resolve
     */
    private function resolveStmt(Stmt $stmt)
    {
        // Resolve this later ;-)
        $stmt->accept($this);
    }

    /**
     * Resolve a single expression
     * @param Expr $expr The expression to resolve
     */
    private function resolveExpr(Expr $expr)
    {
        $expr->accept($this);
    }

    /**
     * Resolve a function declaration
     * @param Function_ $function The function to resolve
     * @param FunctionType|string $type The type of function being resolved
     */
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

    /**
     * Begin a new scope by pushing a new Map onto the scope stack
     * This creates a new environment for variable declarations
     */
    private function beginScope()
    {
        array_push($this->scopes, new Map());
    }

    /**
     * End the current scope by popping the top Map off the scope stack
     * This removes the environment for the completed block
     */
    private function endScope()
    {
        array_pop($this->scopes);
    }

    /**
     * Declare a new variable in the current scope
     * Marks the variable as declared but not yet defined
     * @param Token $name The variable name token to declare
     */
    private function declare(Token $name)
    {
        if (count($this->scopes) === 0) return;

        $scope = $this->scopes[count($this->scopes) - 1];

        if($scope->hasKey($name->lexeme)){
            Phlox::error_($name, "Already a variable with this name in this scope.");
        }

        $scope->put($name->lexeme, false); 
    }

    /**
     * Define a variable in the current scope
     * @param Token $name The variable name token
     */
    private function define(Token $name)
    {
        if(count($this->scopes) === 0) return;

        $this->scopes[count($this->scopes) - 1]->put($name->lexeme ,true);
    }

    /**
     * Resolve a local variable reference
     * @param Expr $expr The expression containing the variable reference
     * @param Token $name The variable name token
     */
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