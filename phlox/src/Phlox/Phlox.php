<?php 
namespace Phlox;

use Phlox\Scanner;

use Exception;
use Phlox\Parser;

class Phlox{
    private static Interpreter $interpreter;
    public static $hadError = false; 
    public static $hadRuntimeError = false;

    public function __construct()
    {
        if(!isset(self::$interpreter)){
            self::$interpreter = new Interpreter();
        }
    }

    public static function main(array $args){
        self::$interpreter = new Interpreter();
        if (count($args) >2 ){
            echo "Usage: php phlox [script]\n";
            exit();
        }elseif (count($args)==2){
            self::runFile($args[1]);

            //indicate an error in the exit code.
            if (self::$hadError) exit(65);
        }else{
            self::runPrompt();
        }
    }

    private static function  runFile(string $path){
        // echo __FUNCTION__." Called\n";
        try{
            if (!file_exists($path)) throw new Exception("File not found");
            $file = fopen($path,"r");
            $source = fread($file,filesize($path));
            self::run($source);

            if(self::$hadError) exit(65);
            if(self::$hadRuntimeError) exit(70);

        } catch (Exception $e){
            echo "Error Reading File.\n";
        }
    }

    private static function  runPrompt(){
        // echo __FUNCTION__." Called\n";
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

    private static function run($source){
        // echo __FUNCTION__." Called\n";
        $scanner = new Scanner($source);
        $tokens = $scanner->scanTokens();

        // foreach($tokens as $token){
        //     echo($token);
        //     echo("\n");
        // }

        $parser = new Parser($tokens);
        $statements = $parser->parse();

        if(Phlox::$hadError) return;

        $resolver = new Resolver(self::$interpreter);
        $resolver->resolve($statements);

        if(Phlox::$hadError) return;

        self::$interpreter->interpret($statements);
        // echo new Ast{}

    }

    public static function error(int $line,string $message)
    {
        self::report($line,"", $message);
    }

    public static function runtimeError(RuntimeError $error)
    {
        echo $error->getMessage()."\n[line ". '$error->token->line'."]\n";
        self::$hadRuntimeError = true;
    }

    private static function report(int $line, string $where, string $message){
        echo("[line ".$line."] Error".$where.": ".$message);
        self::$hadError = true; //Will PHP allow this, if $hadError was not explicitly set to static?
    }

    public static function error_(Token $token, string $message)
    {
        if($token->type == TokenType::EOF){
            Phlox::report($token->line, " at end", $message);
        } else {
            Phlox::report($token->line, " at '". $token->lexeme."'",$message);
        }
    }
}
