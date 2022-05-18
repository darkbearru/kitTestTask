<?php

require_once ("__autoload.php");

use abramenko\router;
use abramenko\authorization;
use abramenko\DB;

$router = new Router ();
$router->addPath ('default', "indexPage");
$router->addPath ('/news/', "newsPage");
$router->run ();

exit;

function indexPage ()
{
    $_auth = new Authorization ();
}

function newsPage ()
{
    echo "You selected news<br />";
}