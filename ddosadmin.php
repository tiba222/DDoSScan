#!/usr/bin/php
<?php
$link = dirname(__FILE__);
if (is_link($link)) {
    $link = readlink($link);
}
define('ROOT_DIR', $link);
include_once(ROOT_DIR . '/lib/CLIManager.php');

$cli = new CLIManager();

if (isset($argv[1])) {
    switch ($argv[1]) {
        case "list":
            $cli->handleList($argv);
            break;
        case "config":
            $cli->handleConfig($argv);
            break;
        case "create":
            $cli->handleCreate($argv);
            break;
        case "delete":
            $cli->handleDelete($argv);
            break;
        case "show":
            $cli->handleShow($argv);
            break;
        case "assign":
            $cli->handleAssign($argv);
            break;
        case "unassign":
            $cli->handleUnassign($argv);
            break;
        case "help":
            $cli->printHelp();
            break;

        default:
            echo("Unknown argument '$argv[1]', use 'ddosadmin help' for help\n");
            break;
    }
} else {
    $cli->printHelp();
}

