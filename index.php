<?php
require_once ("__autoload.php");
date_default_timezone_set ("Asia/Tokyo");

use abramenko\controller;
use abramenko\router;
use abramenko\authorization;
use abramenko\DB;
use abramenko\template;

$controller = new Controller ();
