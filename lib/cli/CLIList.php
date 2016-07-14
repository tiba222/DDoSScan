<?php

include_once (ROOT_DIR . '/lib/DDoSDefinitionManager.php');
include_once (ROOT_DIR . '/lib/RouterManager.php');
include_once (ROOT_DIR . '/lib/ACLManager.php');
include_once (ROOT_DIR . '/lib/DDoSAttackManager.php');
include_once (ROOT_DIR . '/lib/MailAlertManager.php');
include_once (ROOT_DIR . '/lib/ActionManager.php');
include_once (ROOT_DIR . '/lib/cli/CLIOutput.php');

class CLIList {

    protected $cli;

    public function __construct() {
        $this->cli = new CLIOutput();
    }

    public function listDefinitions($argv) {
        $defmgr = new DDoSDefinitionManager();

        $headers = array('Id', 'Description', 'Protocol', 'Source Port', 'Destination Port', 'Primary Identifier', 'NFDump Filter');
        $data = array();

        $pos = 0;
        foreach ($defmgr->listDDoSDefinitions() as $definition) {
            $data[$pos][0] = $definition->id;
            $data[$pos][1] = $definition->description;
            $data[$pos][2] = $definition->protocol;
            $data[$pos][3] = $definition->src_port;
            $data[$pos][4] = $definition->dst_port;
            $data[$pos][5] = $definition->primary_identifier;
            $data[$pos][6] = $definition->nfdump_filter;

            $pos++;
        }
        $this->listToConsole($headers, $data, $argv);
    }

    public function listExclusions($argv) {
        $actionmgr = new ActionManager();

        $headers = array('Id', 'Excluded Subnet', 'Excluded Action');
        $data = array();

        $pos = 0;
        foreach ($actionmgr->listExcludedActions() as $exclusion) {
            $data[$pos][0] = $exclusion->id;
            $data[$pos][1] = $exclusion->target;
            $data[$pos][2] = $exclusion->excluded_action;

            $pos++;
        }
        $this->listToConsole($headers, $data, $argv);
    }

    public function listActions($argv) {
        $actionmgr = new ActionManager();

        $headers = array('Id', 'Description', 'Action', 'Parameters', 'Run Once');
        $data = array();

        $pos = 0;
        foreach ($actionmgr->listActions() as $action) {
            $data[$pos][0] = $action->id;
            $data[$pos][1] = $action->description;
            $data[$pos][2] = $action->action;
            $data[$pos][3] = json_encode($action->action_parameters);
            $data[$pos][4] = $action->once;

            $pos++;
        }
        $this->listToConsole($headers, $data, $argv);
    }

    public function listRouters($argv) {
        $routermgr = new RouterManager();

        $headers = array('Id', 'Name', 'Type', 'Management IP', 'Username', 'Password', 'Enable Password', 'Protected VRF', 'Outside VRF');
        $data = array();

        $pos = 0;
        foreach ($routermgr->listRouters() as $router) {
            $data[$pos][0] = $router->id;
            $data[$pos][1] = $router->name;
            $data[$pos][2] = $router->type;
            $data[$pos][3] = $router->mgmt_ip;
            $data[$pos][4] = $router->username;
            $data[$pos][5] = $router->password;
            $data[$pos][6] = $router->enable_password;
            $data[$pos][7] = $router->protected_vrf;
            $data[$pos][8] = $router->outside_vrf;

            $pos++;
        }
        $this->listToConsole($headers, $data, $argv);
    }

    public function listThresholds($argv) {
        $defmgr = new DDoSDefinitionManager();

        $headers = array('Id', 'DDoS Definition Id', 'Priority', 'BPS Threshold', 'PPS Threshold', 'FPS Threshold', 'Use Trends', 'Trend Window', 'Trend Hits');
        $data = array();

        $pos = 0;
        foreach ($defmgr->listDDoSThresholds() as $threshold) {
            $data[$pos][0] = $threshold->id;
            $data[$pos][1] = $threshold->ddos_type_id;
            $data[$pos][2] = $threshold->priority;
            $data[$pos][3] = $threshold->bps_threshold;
            $data[$pos][4] = $threshold->pps_threshold;
            $data[$pos][5] = $threshold->fps_threshold;
            $data[$pos][6] = $threshold->trend_use;
            $data[$pos][7] = $threshold->trend_window;
            $data[$pos][8] = $threshold->trend_hits;

            $pos++;
        }
        $this->listToConsole($headers, $data, $argv);
    }

    public function listACLs($argv) {
        $aclmgr = new ACLManager();

        $headers = array('Id', 'Router Id', 'Type', 'Name', 'Seq Start', 'Seq End');
        $data = array();

        $pos = 0;
        foreach ($aclmgr->listACLs() as $acl) {
            $data[$pos][0] = $acl->id;
            $data[$pos][1] = $acl->router_id;
            $data[$pos][2] = $acl->type;
            $data[$pos][3] = $acl->name;
            $data[$pos][4] = $acl->seq_start;
            $data[$pos][5] = $acl->seq_end;

            $pos++;
        }
        $this->listToConsole($headers, $data, $argv);
    }
    
    public function listActiveDDoSAttacks($argv) {
        $attackmgr = new DDoSAttackManager();
        $defmgr = new DDoSDefinitionManager();

        $headers = array('Id', 'DDoS Type', 'Time Start', 'Last Traffic', 'Target IP');
        $data = array();

        $pos = 0;
        foreach ($attackmgr->listActiveDDoSAttacks() as $attack) {
            $data[$pos][0] = $attack->id;
            $data[$pos][1] = $defmgr->getDDoSDefinitionById($attack->ddos_type_id)->description;
            $data[$pos][2] = $attack->time_start;
            $data[$pos][3] = $attack->time_last_traffic;
            $data[$pos][4] = $attack->target_ip;

            $pos++;
        }
        $this->listToConsole($headers, $data, $argv);
    }
    
    public function listMailAlerts($argv){
        $mailmgr = new MailAlertManager();
        
        $headers = array('Id', 'CIDR', 'Email');
        $data = array();
        
        $pos = 0;
        foreach($mailmgr->listMailAlerts() as $alert){
            $data[$pos][0] = $alert->id;
            $data[$pos][1] = $alert->target;
            $data[$pos][2] = $alert->email;
        }
        
        $this->listToConsole($headers, $data, $argv);
    }

    protected function listToConsole($headers, $data, $argv) {
        if (isset($argv[3])) {
            $this->cli->CLIPrint($headers, $data, $argv[3]);
        } else {
            $this->cli->CLIPrint($headers, $data);
        }
    }

}
