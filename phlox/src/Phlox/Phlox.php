<?php 
namespace Phlox;

use Phlox\Scanner;

use Exception;
use Phlox\Parser;

/**
 * Main interpreter class for the Lox language
 * Handles program execution and error reporting
 */
class Phlox{
    /**
     * The interpreter instance
     * @var Interpreter
     */
    private static Interpreter $interpreter;

    /**
     * Flag indicating if a syntax error occurred
     * @var bool
     */
    public static $hadError = false; 

    /**
     * Flag indicating if a runtime error occurred
     * @var bool
     */
    public static $hadRuntimeError = false;

    /**
     * Initialize the Phlox interpreter
     */
    public function __construct()
    {
        if(!isset(self::$interpreter)){
            self::$interpreter = new Interpreter();
        }
    }

    /**
     * Main entry point for the Phlox interpreter
     * Handles command line arguments and starts either file execution or REPL mode
     * 
     * @param array $args Command line arguments passed to the program
     */
    public static function main(array $args){
        // Create new interpreter instance
        self::$interpreter = new Interpreter();

        // Check number of command line arguments
        if (count($args) > 2 ){
            // Too many arguments provided
            echo "Usage: php phlox [script]\n";
            exit();
        } elseif (count($args) == 2){
            // Execute the script file specified in args[1]
            self::runFile($args[1]);

            // Exit with error code 65 if syntax/static error occurred
            if (self::$hadError) exit(65);
        } else {
            // No script file provided, start interactive REPL mode
            self::runPrompt();
        }
    }

    /**
     * Execute a Lox source file
     * Reads the file contents and runs them through the interpreter
     * 
     * @param string $path Path to the source file to execute
     */
    private static function runFile(string $path){
        try{
            // Check if file exists before trying to read it
            if (!file_exists($path)) throw new Exception("File not found");
            
            // Open file and read entire contents
            $file = fopen($path,"r");
            $source = fread($file,filesize($path));
            
            // Execute the source code
            self::run($source);

            // Exit with appropriate error codes if errors occurred
            if(self::$hadError) exit(65); // Exit code 65 indicates syntax/static error
            if(self::$hadRuntimeError) exit(70); // Exit code 70 indicates runtime error

        } catch (Exception $e){
            echo "Error Reading File.\n";
        }
    }

    /**
     * Run the REPL (Read-Eval-Print Loop) interactive prompt
     * Repeatedly reads lines of input, executes them, and shows results
     * Continues until Ctrl-D or error occurs
     */
    private static function  runPrompt(){
        try{
            while(true){
               $line = readline("> ");
               if($line === null) break; //How to read Non-Printable ASCII characters in PHP especially Ctrl-D
               self::run($line);
               self::$hadError = false;
            }
        } catch(Exception $e){
            echo "Input Error.\n";
        }
    }

    /**
     * Execute source code through the full interpretation pipeline
     * 1. Scans source into tokens
     * 2. Parses tokens into AST statements
     * 3. Resolves variable bindings
     * 4. Interprets the statements
     * 
     * @param string $source The source code to execute
     */
    private static function run($source){
        $scanner = new Scanner($source);
        $tokens = $scanner->scanTokens();

        $parser = new Parser($tokens);
        $statements = $parser->parse();

        if(Phlox::$hadError) return;

        $resolver = new Resolver(self::$interpreter);
        $resolver->resolve($statements);

        if(Phlox::$hadError) return;

        self::$interpreter->interpret($statements);
    }

    /**
     * Report an error at a specific line number
     * @param int $line The line number where the error occurred
     * @param string $message The error message
     */
    public static function error(int $line,string $message)
    {
        self::report($line,"", $message);
    }

    /**
     * Report a runtime error that occurred during interpretation
     * @param RuntimeError $error The runtime error that occurred
     */
    public static function runtimeError(RuntimeError $error)
    {
        echo $error->getMessage()."\n[line ". '$error->token->line'."]\n";
        self::$hadRuntimeError = true;
    }

    private static function report(int $line, string $where, string $message){
        echo("[line ".$line."] Error".$where.": ".$message);
        self::$hadError = true; //Will PHP allow this, if $hadError was not explicitly set to static?
    }

    /**
     * Report an error at a specific token
     * @param Token $token The token where the error occurred
     * @param string $message The error message
     */
    public static function error_(Token $token, string $message)
    {
        if($token->type == TokenType::EOF){
            Phlox::report($token->line, " at end", $message);
        } else {
            Phlox::report($token->line, " at '". $token->lexeme."'",$message);
        }
    }

}
