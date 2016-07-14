<?php

include_once (ROOT_DIR . '/lib/ActionManager.php');
include_once (ROOT_DIR . '/lib/DDoSDefinitionManager.php');

class CLILink {

    public function __construct() {
        
    }

    public function assignActionToThreshold($argv) {
        $actionmgr = new ActionManager();
        $defmgr = new DDoSDefinitionManager();

        if (!$defmgr->getDDoSThresholdById($argv[4])) {
            echo("ERROR: Threshold with ID $argv[4] does not exist\n");
            return;
        }

        if (!$actionmgr->getActionById($argv[3])) {
            echo("ERROR: Action with ID $argv[3] does not exist\n");
            return;
        }

        if ($actionmgr->linkActionToThreshold($argv[3], $argv[4])) {
            echo("OK\n");
        }
    }

    public function unassignActionFromThreshold($argv) {
        $actionmgr = new ActionManager();

        if ($actionmgr->unlinkActionFromThreshold($argv[3], $argv[4])) {
            echo("OK\n");
        } else {
            echo("Link does not exist, cannot delete\n");
        }
    }

}
