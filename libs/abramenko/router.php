<?php

namespace abramenko;

class Router 
{
    private array $_routerPaths;

    public function __construct () 
    {
        $this->_routerPaths = [];
    }

    public function addPath ($path, $action)
    {
        $this->_routerPaths[$path] = $action;
    }

    public function run ()
    {
        $_url = !empty ($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';

        if (!empty ($this->_routerPaths[$_url])) {
            $_action = $this->_routerPaths[$_url];
            if (function_exists ($_action)) {
                $_action ();
                return true;
            }
        }
        // If nothing found, show default page
        if (!empty ($this->_routerPaths["default"])) {
            $this->_routerPaths["default"]();
        }
    }
}