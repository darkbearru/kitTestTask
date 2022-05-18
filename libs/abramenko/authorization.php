<?php

namespace abramenko;

/**
 * Авторизация пользователя
 */
class Authorization
{
    private bool $_isLogined;

    public function __construct ()
    {
        $_db = new DB ();
        $_db::checkDB ();
    }

    public function isLogined ()
    {
        return $this->_isLogined;
    }
}