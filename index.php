<?php

require_once ("__autoload.php");
date_default_timezone_set ("Asia/Tokyo");

use abramenko\router;
use abramenko\authorization;
use abramenko\DB;
use abramenko\template;

$router = new Router ();
$router->addPath ('default', "indexPage");
$router->addPath ('/news/', "newsPage");
$router->run ();

exit;

function indexPage ()
{
    //echo "Index<br />";
    $template = new Template ();
    $template->show (["hello", "answer"], 'index.html');
}

function newsPage ()
{
    echo "You selected news<br />";
}