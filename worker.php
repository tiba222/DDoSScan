#!/usr/bin/php
<?php
define( 'ROOT_DIR', dirname(__FILE__) );
include_once(ROOT_DIR.'/lib/DDoSScan.php');

$app = new DDoSScan();
$app->run();
