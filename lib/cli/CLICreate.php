<?php

include_once (ROOT_DIR . '/lib/DDoSDefinitionManager.php');
include_once (ROOT_DIR . '/lib/RouterManager.php');
include_once (ROOT_DIR . '/lib/ACLManager.php');
include_once (ROOT_DIR . '/lib/ActionManager.php');
include_once (ROOT_DIR . '/lib/MailAlertManager.php');
include_once (ROOT_DIR . '/lib/cli/CLIOutput.php');

class CLICreate {

    protected $cli;

    public function __construct() {
        $this->cli = new CLIOutput();
    }

    public function createExclusion($argv) {
        $actionmgr = new ActionManager();

        if ($actionmgr->createExcludedAction($argv[3], $argv[4])) {
            echo("OK\n");
        }
    }

    public function createDefinition($argv) {
        $defmgr = new DDoSDefinitionManager();

        if ($defmgr->createDDoSDefinition($argv[3], $argv[4], $argv[5], $argv[6], $argv[7], $argv[8])) {
            echo("OK\n");
        }
    }
    
    public function createThreshold($argv) {
        $defmgr = new DDoSDefinitionManager();

        if ($defmgr->createDDoSThreshold($argv[3], $argv[4], $argv[5], $argv[6], $argv[7], $argv[8], $argv[9], $argv[10])) {
            echo("OK\n");
        }
    }

    public function createRouter($argv) {
        $routermgr = new RouterManager();

        if ($routermgr->createRouter($argv[3], $argv[4], $argv[5], $argv[6], $argv[7], $argv[8], $argv[9], $argv[10])) {
            echo("OK\n");
        }
    }

    public function createACL($argv) {
        $aclmgr = new ACLManager();

        if ($aclmgr->createACL($argv[3], $argv[4], $argv[5], $argv[6], $argv[7])) {
            echo("OK\n");
        }
    }

    public function createAction($argv) {
        $split = explode(';', $argv[5]);
        foreach ($split as $s) {
            $res = explode('=', $s);
            $parameters[$res[0]] = $res[1];
        }

        $actionmgr = new ActionManager();

        if ($actionmgr->createAction($argv[3], $argv[4], $parameters, $argv[6])) {
            echo("OK\n");
        }
    }
    
    public function createMailAlert($argv){
        $mailmgr = new MailAlertManager();
        
        if ($mailmgr->createMailAlert($argv[3], $argv[4])){
            echo("OK\n");
        }
    }

}
