<?php
require_once ("__autoload.php");
date_default_timezone_set ("Asia/Tokyo");

use abramenko\application;
use abramenko\router;
use abramenko\authorization;
use abramenko\DB;
use abramenko\template;
use abramenko\posts;

$application = new Application ();
$template = new Template ();

$data = $application->run (); 

$template->show ($data, "index.html");
