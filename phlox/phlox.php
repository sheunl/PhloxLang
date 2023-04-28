<?php 
namespace phlox;


class Phlox{

    public static function main($args){
        if (count($args) >2 ){
            echo "Usage: php phlox [script]\n";
            exit();
        }elseif (count($args)==2){
            Phlox::runFile($args[1]);
        }else{
            Phlox::runPrompt();
        }
    }

    private static function  runFile(){
        echo __FUNCTION__." Called\n";
    }

    private static function  runPrompt(){
        echo __FUNCTION__." Called\n";
    }
}

Phlox::main($argv);




?>

