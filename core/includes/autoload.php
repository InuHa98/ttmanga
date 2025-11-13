<?php

include INCLUDE_PATH.'/RouteMap.php';
include INCLUDE_PATH.'/functions.php';
include INCLUDE_PATH.'/serverBootHandler.php';
include INCLUDE_PATH.'/serverErrorHandler.php';

spl_autoload_extensions('.php');
spl_autoload_register(function($className){
	$className = str_replace('\\', '/', $className);
	$className = basename($className);
    
    $classes = CLASSES_PATH.'/class.'.$className.'.php';
    if (is_readable($classes)) {
        include_once $classes;
        return;
    }

    $controller = CONTROLLER_PATH.'/'.$className.'.php';
    if (is_readable($controller)) {
        include_once $controller;
    }
});


?>