<?php

namespace abramenko;

class Router 
{
    private $_routerPaths;
    private $_parent;

    public function __construct ($parent) 
    {
        $this->_routerPaths = [];
        $this->_parent = $parent;
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
            if (method_exists ($this->_parent, $_action)) {
                $this->_parent->$_action ();
                return true;
            }
        }
        // If nothing found, show default page
        $_action = $this->_routerPaths["default"];
        if (!empty ($_action)) {
            if (method_exists ($this->_parent, $_action)) {
                $this->_parent->$_action ();
                return true;
            }
        }
    }
}