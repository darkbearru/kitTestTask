<?php
echo "Test";

require_once ("__autoload.inc");

use abramenko\router;

$router = new Router ();
$router->addPath ('default', "indexPage");
$router->addPath ('/news/', "newsPage");
$router->run ();

exit;

function indexPage ()
{
    echo "You see index page<br />";
}

function newsPage ()
{
    echo "You selected news<br />";
}