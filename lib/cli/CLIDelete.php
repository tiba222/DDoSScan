<?php

include_once (ROOT_DIR . '/lib/cli/CLIOutput.php');
include_once (ROOT_DIR . '/lib/DDoSDefinitionManager.php');
include_once (ROOT_DIR . '/lib/RouterManager.php');
include_once (ROOT_DIR . '/lib/ActionManager.php');
include_once (ROOT_DIR . '/lib/MailAlertManager.php');
include_once (ROOT_DIR . '/lib/ACLManager.php');

class CLIDelete {

    protected $cli;

    public function __construct() {
        $this->cli = new CLIOutput();
    }

    public function deleteACL($argv) {
        $aclmgr = new ACLManager();
        if ($aclmgr->deleteACLEntryById($argv[3])) {
            echo("OK\n");
        }
    }

    public function deleteAction($argv) {
        $actionmgr = new ActionManager();
        if ($actionmgr->getActionUseCount($argv[3]) > 0) {
            echo("Action is still used by threshold(s), cannot delete\n");
            return;
        }

        if ($actionmgr->deleteActionById($argv[3])) {
            echo("OK\n");
        }
    }

    public function deleteDefinition($argv) {
        $defmgr = new DDoSDefinitionManager();
        if ($defmgr->deleteDDoSDefinitionById($argv[3])) {
            echo("OK\n");
        }
    }

    public function deleteExclusion($argv) {
        $actionmgr = new ActionManager();
        if ($actionmgr->deleteExcludedActionById($argv[3])) {
            echo("OK\n");
        }
    }

    public function deleteRouter($argv) {
        $routermgr = new RouterManager();
        if ($routermgr->deleteRouterById($argv[3])) {
            echo("OK\n");
        }
    }

    public function deleteThreshold($argv) {
        $defmgr = new DDoSDefinitionManager();
        if ($defmgr->deleteThresholdById($argv[3])) {
            echo("OK\n");
        }
    }

    public function deleteMailAlert($argv) {
        $mailmgr = new MailAlertManager();

        if ($mailmgr->deleteMailAlertById($argv[3])) {
            echo("OK\n");
        }
    }

}
