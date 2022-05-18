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
    echo "<pre>";
    print_r ($_auth->Login ('admin', 'password'));
    echo "</pre>";
    echo "<pre>";
    print_r ($_auth->Logout ());
    echo "</pre>";
}

function newsPage ()
{
    echo "You selected news<br />";
}