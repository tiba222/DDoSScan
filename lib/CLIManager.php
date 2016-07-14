<?php

include_once (ROOT_DIR . '/lib/cli/CLIList.php');
include_once (ROOT_DIR . '/lib/cli/CLICreate.php');
include_once (ROOT_DIR . '/lib/cli/CLIDelete.php');
include_once (ROOT_DIR . '/lib/cli/CLIShow.php');
include_once (ROOT_DIR . '/lib/cli/CLILink.php');
include_once (ROOT_DIR . '/lib/cli/CLIConfig.php');
include_once (ROOT_DIR . '/lib/ConfigManager.php');

class CLIManager {

    protected $list;
    protected $delete;
    protected $create;
    protected $show;
    protected $link;
    protected $config;
    protected $configmanager;

    public function __construct() {
        $this->list = new CLIList();
        $this->create = new CLICreate();
        $this->delete = new CLIDelete();
        $this->show = new CLIShow();
        $this->link = new CLILink();
        $this->config = new CLIConfig();
        $this->configmanager = new ConfigManager();
    }

    public function handleList($argv) {
        switch ($argv[2]) {
            case 'active-attacks':
                $this->list->listActiveDDoSAttacks($argv);
                break;
            case 'definitions':
                $this->list->listDefinitions($argv);
                break;
            case 'exclusions':
                $this->list->listExclusions($argv);
                break;
            case 'mail-alerts':
                $this->list->listMailAlerts($argv);
                break;
            case 'actions':
                $this->list->listActions($argv);
                break;
            case 'routers':
                $this->list->listRouters($argv);
                break;
            case 'acls':
                $this->list->listACLs($argv);
                break;
            case 'thresholds':
                $this->list->listThresholds($argv);
                break;

            default:
                echo("Unknown argument '$argv[2]', use 'ddosadmin help' for help\n");
                break;
        }
    }

    public function handleConfig($argv) {
        switch ($argv[2]) {
            case 'show':
                $this->config->showConfig($argv);
                break;
            case 'add-subnet':
                $this->config->addSubnet($argv);
                break;
            case 'delete-subnet':
                $this->config->deleteSubnet($argv);
                break;
            case 'change-setting':
                $this->config->changeSetting($argv);
                break;

            default:
                echo("Unknown argument '$argv[2]', use 'ddosadmin help' for help\n");
                break;
        }
    }

    public function handleCreate($argv) {
        switch ($argv[2]) {
            case 'definition':
                $this->create->createDefinition($argv);
                break;
            case 'exclusion':
                $this->create->createExclusion($argv);
                break;
            case 'mail-alert':
                $this->create->createMailAlert($argv);
                break;
            case 'action':
                $this->create->createAction($argv);
                break;
            case 'router':
                $this->create->createRouter($argv);
                break;
            case 'acl':
                $this->create->createACL($argv);
                break;
            case 'threshold':
                $this->create->createThreshold($argv);
                break;

            default:
                echo("Unknown argument '$argv[2]', use 'ddosadmin help' for help\n");
                break;
        }
    }

    public function handleDelete($argv) {
        switch ($argv[2]) {
            case 'acl':
                $this->delete->deleteACL($argv);
                break;
            case 'action':
                $this->delete->deleteAction($argv);
                break;
            case 'definition':
                $this->delete->deleteDefinition($argv);
                break;
            case 'exclusion':
                $this->delete->deleteExclusion($argv);
                break;
            case 'mail-alert':
                $this->delete->deleteMailAlert($argv);
                break;
            case 'router':
                $this->delete->deleteRouter($argv);
                break;
            case 'threshold':
                $this->delete->deleteThreshold($argv);
                break;

            default:
                echo("Unknown argument '$argv[2]', use 'ddosadmin help' for help\n");
                break;
        }
    }

    public function handleAssign($argv) {
        switch ($argv[2]) {
            case 'action':
                $this->link->assignActionToThreshold($argv);
                break;

            default:
                echo("Unknown argument '$argv[2]', use 'ddosadmin help' for help\n");
                break;
        }
    }

    public function handleUnassign($argv) {
        switch ($argv[2]) {
            case 'action':
                $this->link->unassignActionFromThreshold($argv);
                break;

            default:
                echo("Unknown argument '$argv[2]', use 'ddosadmin help' for help\n");
                break;
        }
    }

    public function handleShow($argv) {
        switch ($argv[2]) {
            case 'definition':
                $this->show->showDefinition($argv);
                break;
            case 'router':
                $this->show->showRouter($argv);
                break;
            case 'threshold':
                $this->show->showThreshold($argv);
                break;

            default:
                echo("Unknown argument '$argv[2]', use 'ddosadmin help' for help\n");
                break;
        }
    }

    public function printHelp() {
        $version = $this->configmanager->getVersion();
        echo("DDoSScan CLI Interface\n"
        . "Version: $version \n\n"
        . "Available Commands:\n\n"
        . "ddosadmin assign action <action_id> <threshold_id>\n"
        . "\n"
        . "ddosadmin config show\n"
        . "ddosadmin config add-subnet <cidr> <description>\n"
        . "ddosadmin config delete-subnet <cidr>\n"
        . "ddosadmin config change-setting <setting> <value>\n"
        . "\n"
        . "ddosadmin create acl <router_id> <name> <type: outside or inside> <seq_start> <seq_end>\n"
        . "ddosadmin create action <description> <action> <action parameters: key=value;key=value> <once>\n"
        . "ddosadmin create definition <description> <protocol> <source port> <destination port> <nfdump filter> <primary identifier>\n"
        . "ddosadmin create exclusion <cidr> <excluded action>\n"
        . "ddosadmin create mail-alert <cidr> <email>\n"
        . "ddosadmin create router <name> <type> <mgmt_ip> <username> <password> <enable_password> <protected_vrf> <outside_vrf>\n"
        . "ddosadmin create threshold <ddos_definition_id> <priority> <bps> <pps> <fps> <trend_use> <trend_window> <trend_hits>\n"
        . "\n"
        . "ddosadmin delete acl <id>\n"
        . "ddosadmin delete action <id>\n"
        . "ddosadmin delete definition <id>\n"
        . "ddosadmin delete exclusion <id>\n"
        . "ddosadmin delete router <id>\n"
        . "ddosadmin delete mail-alert <id>\n"
        . "ddosadmin delete threshold <id>\n"
        . "\n"
        . "ddosadmin list acls [json]\n"
        . "ddosadmin list actions [json]\n"
        . "ddosadmin list active-attacks [json]\n"
        . "ddosadmin list definitions [json]\n"
        . "ddosadmin list exclusions [json]\n"
        . "ddosadmin list mail-alerts\n"
        . "ddosadmin list routers [json]\n"
        . "ddosadmin list thresholds [json]\n"
        . "\n"
        . "ddosadmin unassign action <action_id> <threshold_id>\n"
        . "\n"
        . "ddosadmin show definition <id>\n"
        . "ddosadmin show router <id>\n"
        . "ddosadmin show threshold <id>\n"
        . "\n");
    }

}
