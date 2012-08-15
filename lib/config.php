<?php
// Example config file.

// Should contain this. 
header("Content-Type: text/html; charset=UTF-8");

/**
 * Global settings - Individual config. Just nice to have in a config file.
 */
define("DEBUG", true);

if(DEBUG) {
    // set debug settings
    ini_set("display_errors", "1");
    // Report all PHP errors (see changelog)
    error_reporting(E_ALL);
    ini_set('error_reporting', E_ALL);
} else {
    // deactivate error messages, debug info etc..
    // set debug settings
    ini_set("display_errors", "0");
    // Report all PHP errors (see changelog)
    error_reporting(0);
    ini_set('error_reporting', 0);
}

// Added a magic function (autoload) to not have to import all the classes
// if there no use for them.
// function __autoload($class) {
//     echo $class;
//     $class = './lib/' . str_replace('\\', '/', $class) . '.class.php';
//     require_once($class);
// }
// either you have this autoload-function or you must require/include all files.

spl_autoload_register(function($className) { 
    require_once('./lib/' . str_replace('\\', '/', ltrim($className, '\\')) . '.class.php'); 
}); 