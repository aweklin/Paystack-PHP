<?php

// ensure the user is running a supported PHP version
if (PHP_VERSION < 7) {
    die('You are currently running PHP version \"' . PHP_VERSION . '\". This app works only with PHP 7 and above.');
}

// define global constants that it used throughout the app
if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}
define('PAYSTACK_LIB_ROOT', dirname(__FILE__));
define('PAYSTACK_BASE_URL', 'https://api.paystack.co/');

// autoload classes with anonymous function
spl_autoload_register(function($className) {
    $className = str_replace("Aweklin\\Paystack", "Src", $className);
    $classArray = explode('\\', $className);
    $class = array_pop($classArray);
    $subPath = mb_strtolower(implode(DS, $classArray));

    $classPath = PAYSTACK_LIB_ROOT . DS . $subPath . DS . $class . '.php';
    if (file_exists($classPath)) {
        include_once ($classPath);
        return;
    }

});