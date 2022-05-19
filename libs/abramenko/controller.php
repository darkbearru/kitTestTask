<?php

namespace abramenko;

class Controller
{
    private $_auth;
   
    public function __construct ()
    {

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
        $template = new Template ();
        $this->_auth = new Authorization ();
        
        if (!empty($_GET['request']) && $this->_auth->isLogined ()) {
            $method = $_GET['request'];
            $db = new DB ();
            $id = intval ($_GET['id']);
            $upid = intval ($_GET['upid']);

            $name = addslashes ($_GET['name']);
            $text = $_GET['text'];

            $text = str_replace('\n', "\n", $text);

            $text = addslashes ($text);

            $results = false;

            switch ($method) {
                case "POST" : {
                    $results = $db->Query ("insert into posts (upid, name, text, changed) values('{$upid}', '{$name}', '{$text}', now())");
                    if (!$db->isError ()){
                        $results = [
                            "result" => "ok",
                            "id"    => $db->insertID (),
                            "upid"  => (!empty($upid) ? $upid : 0)
                        ];
                    }
                    break;
                }
                case "DELETE" : {
                    $results = $db->Query ("delete from posts where upid='{$id}'");
                    if (!$db->isError ()) {
                        $results = $db->Query ("delete from posts where id='{$id}'");
                    }
                    break;
                }
                case "PUT" : {
                    $results = $db->Query ("update posts set name='{$name}', text='{$text}', changed=now() where id='{$id}'");
                    break;
                }
            }
            if (!$results && $db->isError ()) {
                $results = ["error" => $db->errorsList ()];
            } else {
                if (!$results) {
                    $results = [ "result" => "ok" ];
                }
            }
            $template->show ($results);

        } else {
            $results = $this->getTree ();
            $template->show ($results);
        }
        
    }

    private function collapseTree ($tree, $upid = 0)
    {
        $result = [];
        foreach ($tree as $item) {
            if ($item->upid == $upid) {
                $childs = $this->collapseTree ($tree, $item->id);
                if (!empty ($childs)) {
                    $item->childs = $childs;
                }
                $result[] = (object) $item;
            }
            /*            
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
            */
        }
        return $result;
    }

    private function checkLoginLogout ()
    {
        if (!$this->_auth->isLogined () && $_SERVER["REQUEST_METHOD"] === "POST") {
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
            if ($db->isError ()) {
                $results = [
                    'error' => $db->errorsList ()
                ];
            }
        }
        return $results;
    }
    
}