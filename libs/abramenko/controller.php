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
        $router->addPath ('/admin/', "administratorPage");
        $router->addPath ('/api/', "pageAPI");
        $router->run ();
    }

    public function pageIndex ()
    {
        $template = new Template ();
        $template->show (["hello", "answer"], 'index.html');
    }

    public function pageAPI ()
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
}