<?php 

/**
 * PSR-4 compliant autoloader
 * Automatically loads class files by converting namespace separators to directory separators
 * and appending .php extension
 * 
 * @param string $class_name Fully qualified class name including namespace
 */
spl_autoload_register(function ($class_name){
    $class_name = str_replace('\\', '/', $class_name);
    include $class_name.'.php';
});