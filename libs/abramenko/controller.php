<?php

namespace abramenko;

class Controller
{
    private $_auth;
    private $_method;

    public function __construct ()
    {
        $this->_method = $_SERVER["REQUEST_METHOD"];

        $router = new Router ($this);
        $router->addPath ('default', "pageIndex");
        $router->addPath ('/admin/', "pageAdmin");
        $router->addPath ('/api/', "pageAPI");
        $router->run ();
    }

    public function pageIndex ()
    {
        $template = new Template ();
        $template->show (
            [
                "title" => "Тестовое задание",
                "caption" => "Тестовое задание для компании «КИТ»",
                "data-list" => []
            ], 
            'index.html'
        );
    }

    public function pageAdmin ()
    {
        $data = [
            "title" => "Администрирование",
            "caption" => "Тестовое задание для компании «КИТ». Администрирование",
            "data-list" => [],
            "login-form" => []
        ];
        $this->_auth = new Authorization ();
        
        $this->checkLoginLogout ();

        if ($this->_auth->isLogined ()) {
            $data["is-logined"] =  (object) ["show" => true];
            $data["login-form"] = [];
        } else {
            $data["is-logined"] = [];
            $data["login-form"] = (object) ["show" => true];
        }

        $template = new Template ();
        $template->show ($data, 'admin.html');
    }

    public function pageAPI ()
    {
        $results = $this->getTree ();

        $template = new Template ();
        $template->show ($results);
    }

    private function collapseTree ($tree)
    {
        $result = [];
        foreach ($tree as $item) {
            if ($item->upid != 0) {
                if (!empty ($result[$item->upid])) {
                    if (empty ($result[$item->upid]->childs)) {
                        $result[$item->upid]->childs = [];
                    }
                    $result[$item->upid]->childs[] = $item;                
                }
            } else {
                $result[$item->id] = (object) $item;
            }
        }
        return $result;
    }

    private function checkLoginLogout ()
    {
        if (!$this->_auth->isLogined () && $this->_method === "POST") {
            if (!empty ($_POST['login']) && !empty ($_POST['password'])) {
                $_user = addslashes ($_POST['login']);
                $_password = addslashes ($_POST['password']);
                $this->_auth->Login ($_user, $_password);
            }
        } else if (!empty ($_POST['logout'])) {
            $this->_auth->Logout ();
        }
    }

    function getTree ()
    {
        $db = new DB ();
        $results = $db->Query ('select id, upid, name, text from posts order by id');
        if ($results) {
            $results = $this->collapseTree ($results);
        } else {
            $results = (object) [
                'error' => $db->errorsList ()
            ];
        }
        return $results;
    }
    
}