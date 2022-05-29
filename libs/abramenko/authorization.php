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
        $this->checkLogined ();
    }

    public function isLogined ()
    {
        return $this->_isLogined;
    }

    public function Login ($user, $password)
    {
        $password = md5($password);
        $result = $this->_db->Query ("select id, login from users where login='{$user}' and password=password('{$password}')", true);
        $error = false;

        if (!empty ($result)) {
            $this->_user = $result;
            $this->_isLogined = true;
            $this->_db->Query ("delete from sessions where user_id='{$result->id}'");
    
            $this->_db->Query ("insert into sessions (id, user_id, activity) values ('{$this->_sessionID}', '{$result->id}', now())");
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

    protected function checkLogined ()
    {
        // Удаляем все старые сессии
        $this->_db->Query ("DELETE FROM sessions WHERE activity<DATE_ADD(now(), INTERVAL -30 MINUTE)");
        if (!$this->_isLogined) {
            $result = $this->_db->Query (
                "SELECT users.login FROM sessions LEFT JOIN users on sessions.user_id=users.id
                WHERE sessions.id='{$this->_sessionID}'", 
                true);
            if (!empty ($result)) {
                $this->_user = $result;
                $this->_isLogined = true;
                $this->_qb->Query ("UPDATE sessions set activity=now() where id='{$this->_sessionID}'");
            }
        }
    }
}