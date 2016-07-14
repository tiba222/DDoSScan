<?php

include_once (ROOT_DIR . '/lib/cli/CLIOutput.php');
include_once (ROOT_DIR . '/lib/DDoSDefinitionManager.php');
include_once (ROOT_DIR . '/lib/RouterManager.php');
include_once (ROOT_DIR . '/lib/ActionManager.php');
include_once (ROOT_DIR . '/lib/ACLManager.php');

class CLIShow {

    protected $cli;

    public function __construct() {
        $this->cli = new CLIOutput();
    }

    public function showDefinition($argv) {
        $defmgr = new DDoSDefinitionManager();

        $objh = array('Id', 'Description', 'Protocol', 'Source Port', 'Destination Port', 'Primary Identifier', 'NFDump Filter');
        $definition = $defmgr->getDDoSDefinitionById($argv[3]);

        $defdata = array();
        $defdata[] = $definition->id;
        $defdata[] = $definition->description;
        $defdata[] = $definition->protocol;
        $defdata[] = $definition->src_port;
        $defdata[] = $definition->dst_port;
        $defdata[] = $definition->primary_identifier;
        $defdata[] = $definition->nfdump_filter;

        $this->cli->CLIPrint($objh, $defdata, 'object');

        echo("\nTresholds defined for this DDoS definition:\n\n");

        $trsh = array('Id', 'Priority', 'BPS Threshold', 'PPS Threshold', 'FPS Threshold', 'Use Trends', 'Trend Window', 'Trend Hits');

        $data = array();
        $pos = 0;
        foreach ($defmgr->listDDoSThresholdsByDDoSDefinitionId($definition->id) as $threshold) {
            $data[$pos][0] = $threshold->id;
            $data[$pos][1] = $threshold->priority;
            $data[$pos][2] = $threshold->bps_threshold;
            $data[$pos][3] = $threshold->pps_threshold;
            $data[$pos][4] = $threshold->fps_threshold;
            $data[$pos][5] = $threshold->trend_use;
            $data[$pos][6] = $threshold->trend_window;
            $data[$pos][7] = $threshold->trend_hits;

            $pos++;
        }

        $this->cli->CLIPrint($trsh, $data, 'column');
    }

    public function showRouter($argv) {
        $routermgr = new RouterManager();

        $objh = array('Id', 'Name', 'Type', 'Management IP', 'Username', 'Password', 'Enable Password', 'Protected VRF', 'Outside VRF');
        $router = $routermgr->getRouterById($argv[3]);

        $routerdata = array();

        $routerdata[] = $router->id;
        $routerdata[] = $router->name;
        $routerdata[] = $router->type;
        $routerdata[] = $router->mgmt_ip;
        $routerdata[] = $router->username;
        $routerdata[] = $router->password;
        $routerdata[] = $router->enable_password;
        $routerdata[] = $router->protected_vrf;
        $routerdata[] = $router->outside_vrf;

        $this->cli->CLIPrint($objh, $routerdata, 'object');

        echo("\nACLs defined for this router:\n\n");

        $aclmgr = new ACLManager();

        $aclhdr = array('Id', 'Router Id', 'Type', 'Name', 'Seq Start', 'Seq End');
        $data = array();

        $pos = 0;
        foreach ($aclmgr->listACLsByRouterId($argv[3]) as $acl) {
            $data[$pos][0] = $acl->id;
            $data[$pos][1] = $acl->router_id;
            $data[$pos][2] = $acl->type;
            $data[$pos][3] = $acl->name;
            $data[$pos][4] = $acl->seq_start;
            $data[$pos][5] = $acl->seq_end;

            $pos++;
        }
        $this->cli->CLIPrint($aclhdr, $data, 'column');
    }

    public function showThreshold($argv) {
        $defmgr = new DDoSDefinitionManager();

        $objh = array('Id', 'DDoS Definition Id', 'Priority', 'BPS Threshold', 'PPS Threshold', 'FPS Threshold', 'Use Trends', 'Trend Window', 'Trend Hits');
        $threshold = $defmgr->getDDoSThresholdById($argv[3]);

        $thrdata = array();
        $thrdata[] = $threshold->id;
        $thrdata[] = $threshold->ddos_type_id;
        $thrdata[] = $threshold->priority;
        $thrdata[] = $threshold->bps_threshold;
        $thrdata[] = $threshold->pps_threshold;
        $thrdata[] = $threshold->fps_threshold;
        $thrdata[] = $threshold->trend_use;
        $thrdata[] = $threshold->trend_window;
        $thrdata[] = $threshold->trend_hits;

        $this->cli->CLIPrint($objh, $thrdata, 'object');

        echo("\nActions defined for this threshold:\n\n");

        $actionhdr = array('Id', 'Description', 'Action', 'Parameters', 'Run Once');

        $actionmgr = new ActionManager();

        $data = array();
        $pos = 0;
        foreach ($actionmgr->listActionsByThresholdId($threshold->id) as $action) {
            $data[$pos][0] = $action->id;
            $data[$pos][1] = $action->description;
            $data[$pos][2] = $action->action;
            $data[$pos][3] = json_encode($action->action_parameters);
            $data[$pos][4] = $action->once;

            $pos++;
        }

        $this->cli->CLIPrint($actionhdr, $data, 'column');
    }

}
