<?php

namespace abramenko;

class Application
{
    private $_auth;
    private $_router;
    private $_pageData;
    private $_templateFileName = 'index.html';
   
    public function __construct ()
    {
        /**
         * Создаёт Роутинг, а также привязываем методы обработки к определённым путям
         */
        $router = new Router ($this, "pageIndex");

        $router->get ('/admin/', "getAdmin");
        $router->post ('/admin/login/', "loginAdmin");
        $router->post ('/admin/logout/', "logoutAdmin");
        $router->get ('/api/', "getAPI");
        $router->put ('/api/', "putAPI");
        $router->delete ('/api/', "deleteAPI");
        $router->post ('/api/', "postAPI");

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

    /**
     * Выводим начальную страницу администрирования
     */
    public function getAdmin ()
    {
        $this->_templateFileName = 'admin.html';
        // Для залогиненных получаем данные
        if ($this->_auth->isLogined ()) {
            // Поскольку получение данных дерева идетично как для админов, так и для обычных вызываем формирование индексной страницы
            $this->pageIndex ();
            $this->_pageData["is-logined"] =  (object) ["show" => true];
        } else {
            $this->_pageData["login-form"] = (object) ["show" => true];
        }
        // Переопредляем необходимые данные
        $this->_pageData["title"]   = "Администрирование";
        $this->_pageData["caption"] = "Тестовое задание для компании «КИТ». Администрирование";

        return $this->_pageData;
    }

    public function loginAdmin ($params)
    {
        if (!empty ($_POST['login']) && !empty ($_POST['password'])) {
            $_user = addslashes ($_POST['login']);
            $_password = addslashes ($_POST['password']);
            $this->_auth->Login ($_user, $_password);
        }
        $this->getAdmin ();
    }
    
    public function logoutAdmin ($params)
    {
        $this->_auth->Logout();
        $this->getAdmin ();
    }

    public function getAPI ()
    {
        $posts = new Posts ();
        $this->_auth = new Authorization ();

        return [
            "json"  => true,
            "is-logined" => $this->_auth->isLogined (),
            "data-list"  => $posts->getTree ()
        ];
    }

    public function putAPI ($params = false)
    {
        if ( $result = $this->IsNotAuthorizationCheck($params)) {
            return $this->JsonAnswer ($result);
        }

        $posts = new Posts ();
        return $this->JsonAnswer ($posts->Update ($params->body));
    }
    
    public function postAPI ($params = false)
    {
        if ( $result = $this->IsNotAuthorizationCheck($params)) {
            return $this->JsonAnswer ($result);
        }

        $posts = new Posts ();
        return $this->JsonAnswer ($posts->Insert ($params->body));
    }

    public function deleteAPI ($params = false)
    {
        if ( $result = $this->IsNotAuthorizationCheck($params)) {
            return $this->JsonAnswer ($result);
        }

        $posts = new Posts ();
        return $this->JsonAnswer ($posts->Delete ($params->body->id));
    }

    public function templateFileName ()
    {
        return $this->_templateFileName;
    }

    protected function JsonAnswer ($data)
    {
        $data = (array) $data;
        $data["json"] = true;
        return $data;
    }

    protected function IsNotAuthorizationCheck ($params = false)
    {
        $this->_auth = new Authorization ();
        if (!$this->_auth->isLogined ()) {
            return [
                "json"  => true,
                "error" => ["Требуется авторизация"]
            ];            
        }
        if (empty ($params)) {
            return [
                "json"  => true,
                "error" => ["Нет необходимых параметров"]
            ];            
        }
        return false;
    }

}