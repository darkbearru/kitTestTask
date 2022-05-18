<?php

require_once ("__autoload.php");
date_default_timezone_set ("Asia/Tokyo");

use abramenko\router;
use abramenko\authorization;
use abramenko\DB;
use abramenko\template;

$router = new Router ();
$router->addPath ('default', "indexPage");
$router->addPath ('/admin/', "administratorPage");
$router->addPath ('/get/', "getPosts");
$router->run ();

exit;

function indexPage ()
{
    //echo "Index<br />";
    $template = new Template ();
    $template->show (["hello", "answer"], 'index.html');
}

function administratorPage ()
{
    echo "You selected Admin Section<br />";
}

function getPosts ()
{
    $db = new DB ();
    $results = $db->Query ('select id, upid, name, text from posts order by id');
    if ($results) {
        $results = collapseTree ($results);
    } else {
        $results = (object) [
            'error' => $db->errorsList ()
        ];
    }
    $template = new Template ();
    $template->show ($results);
}

function collapseTree ($tree)
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