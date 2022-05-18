<?php

function customAutoloader ($class) 
{
    $_root = $_SERVER['DOCUMENT_ROOT'];
    $class =str_replace ("\\", "/", $class);
    include ("{$_root}/libs/{$class}.php"); 
} 

spl_autoload_register ('customAutoloader');