<?php

namespace abramenko;

session_start();

/**
 * Авторизация пользователя
 */
class Authorization
{
    private $_isLogined;
    private $_sessionID;
    private $_db;
    private $_user;

    public function __construct ()
    {
        $this->_db = new DB ();
        $this->_db->checkDB ();

        $this->_sessionID = session_id ();        
    }

    public function isLogined ()
    {
        if (!$this->_isLogined) {
            $result = $this->_db->Query ("select id, login from users where session='{$this->_sessionID}'", true);
            if (!empty ($result)) {
                $this->_user = $result;
                $this->_isLogined = true;
            }
        }
        return $this->_isLogined;
    }

    public function Login ($user, $password)
    {
        $result = $this->_db->Query ("select id, login from users where login='{$user}' and password=password('{$password}')", true);
        $error = false;

        if (!empty ($result)) {
            $this->_user = $result;
            $this->_isLogined = true;
            $this->_db->Query ("update users set session='{$this->_sessionID}' where id='{$result->id}'");
        } else {
            if (!$this->_db->isError ()) {
                $error = (object) ['error' => "Неверный пользователь или пароль"];
            } else {
                $error = $this->_db->errorsList ();
            }
        }
        return (object) [
            'error' => $error,
            'result' => $result
        ];
    }

    public function Logout ()
    {
        if (!$this->_isLogined) return (object) ['error' => false, 'result' => []];
        
        $error = false;
        $result = $this->_db->Query ("update users set session='' where session='{$this->_sessionID}'", true);

        unset ($this->_user);
        $this->_isLogined = false;

        if ($this->_db->isError ()) {
            $error = $this->_db->errorsList ();
        }
        return (object) [
            'error' => $error,
            'result' => (object) $result
        ];
    }
}