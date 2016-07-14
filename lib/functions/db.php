<?php

function getDB() {
    include (ROOT_DIR . '/config.php');
    $db = new PDO("mysql:host={$config['db_host']};dbname={$config['db_name']}", $config ['db_user'], $config ['db_pass']);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    return $db;
}
