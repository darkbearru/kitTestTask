<?php

namespace abramenko;

class Application
{
    private $_auth;
    private $_router;
    private $_pageData;
   
    public function __construct ()
    {
        $router = new Router ($this, "pageIndex");

        $router->get ('/admin/', "getAdmin");
        $router->post ('/admin/', "postAdmin");
        $router->get ('/api/', "getAPI");
        $router->put ('/api/', "getAPI");
        $router->delete ('/api/', "getAPI");
        $router->post ('/api/', "getAPI");

        $this->_auth = new Authorization ();
        $this->_router = $router;
        $this->_pageData = [
            "title"     => "Тестовое задание",
            "caption"   => "Тестовое задание для компании «КИТ»",
            "data-list" => [],
            "is-logined"=> [],
            "login-form"=> []
        ];
    }

    public function run ()
    {
        return $this->_router->run ();
    }

    public function pageIndex ()
    {
        $posts = new Posts ();
        $tree = $posts->getTree ();
        if (empty ($tree["error"])) {
            $this->_pageData["data-list"] = $posts->htmlTree ($tree);
        }
        return $this->_pageData;
    }

    public function getAdmin ()
    {
        $this->_pageData["title"]   = "Администрирование";
        $this->_pageData["caption"] = "Тестовое задание для компании «КИТ». Администрирование";
        

        if ($this->_auth->isLogined ()) {
            $this->_pageData["is-logined"] =  (object) ["show" => true];
        } else {
            $this->_pageData["login-form"] = (object) ["show" => true];
        }

        return $data;
    }

    public function postAdmin ($params)
    {
        $this->checkLoginLogout ();
        $this->getAdmin ();
    }

    public function getAPI ()
    {
        $this->_auth = new Authorization ();
        return [
            "json"  => true,
            "is-logined" => $this->_auth->isLogined (),
            "data"  => $this->getTree ()
        ];
    }

    public function putAPI ($params = false)
    {
        $this->_auth = new Authorization ();
        if (!$this->_auth->isLogined ()) {
            return [
                "error" => ["Требуется авторизация"]
            ];
        }
        echo '<pre>';
        print_r ($params);
        echo '</pre>';

    }

    public function pageAPI2 ()
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
                    $results = $db->Query ("update posts set upid='{$upid}', name='{$name}', text='{$text}', changed=now() where id='{$id}'");
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