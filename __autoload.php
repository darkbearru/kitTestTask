<?php

function customAutoloader ($class) 
{
    $_root = $_SERVER['DOCUMENT_ROOT'];
    $class =str_replace ("\\", "/", $class);
    include ("{$_root}/libs/{$class}.php"); 

    // echo "{$_root}/libs/{$class}.php is Loaded...<br />\r\n";
}

spl_autoload_register ('customAutoloader');
