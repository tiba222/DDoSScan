<?php

include_once (ROOT_DIR . '/lib/handlers/ActionHandler.php');
include_once (ROOT_DIR . '/lib/RouterManager.php');
include_once (ROOT_DIR . '/lib/ACLManager.php');
include_once (ROOT_DIR . '/lib/MitigationManager.php');
include_once (ROOT_DIR . '/lib/DDoSAttackManager.php');
include_once (ROOT_DIR . '/lib/models/DDoSDefinition.php');
include_once (ROOT_DIR . '/lib/functions/log.php');

class ACLMitigation implements ActionHandler {

    protected $routermanager;
    protected $aclmanager;
    protected $mitigationmanager;
    protected $router_types;

    public function __construct() {
        $this->routermanager = new RouterManager();
        $this->aclmanager = new ACLManager();
        $this->mitigationmanager = new MitigationManager();

        $this->router_types = array('ios_xr');
    }

    public function preScan() {
        return;
    }

    public function onDDoS($action, $data, $definition, $attack) {
        $id = $this->mitigationmanager->checkForActiveMitigation($attack);
        if ($id) {
            logToSyslog("ACLMitigation: Mitigation for this DDoS attack ($definition->description against $data[4]) was already active", LOG_INFO);
            $this->mitigationmanager->updateMitigationHistory($id, $action->action_parameters);

            return true;
        }

        foreach ($this->routermanager->listRouters() as $router) {
            return $this->_addToRouterACLs($router, $action, $data, $definition, $attack);
        }
        
        $this->mitigationmanager->addMitigationHistory($attack, TRUE, $action->action_parameters, 'Automatically Mitigated');
    }

    public function postScan() {
        logToSyslog("ACLMitigation: Cleaning up expired entries", LOG_INFO);

        foreach ($this->mitigationmanager->listExpiredMitigations() as $expired) {
            foreach ($this->aclmanager->listACLEntriesByDDoSAttackId($expired['ddos_attack_id']) as $entry) {
                $acl = $this->aclmanager->getACLById($entry->acl_id);
                $router = $this->routermanager->getRouterById($acl->router_id);

                if ($this->_deleteFromRouterACLs($router, $acl, $entry)) {
                    $this->mitigationmanager->setInactive($expired['id']);
                    $this->aclmanager->deleteACLEntryById($entry->id);
                }
            }
        }
    }

    protected function _addToRouterACLs($router, $action, $data, $definition, $attack) {
        foreach ($this->aclmanager->listACLsByRouterId($router->id) as $acl) {
            if (!in_array($router->type, $this->router_types)) {
                logToSyslog("Router type $router->type is not supported", LOG_ERR);
                return false;
            }

            $seq = $this->aclmanager->getFreeSeqNumber($acl);
            $parameters = $this->_getAddExpectParameters($acl, $router, $action, $data, $definition, $seq);

            if ($definition->src_port != "any" && $definition->dst_port == "any") {
                $cmd = "/usr/bin/expect " . ROOT_DIR . "/expect/$router->type/add_src_acl_entry.expect " . $parameters;
            } elseif ($definition->src_port == "any" && $definition->dst_port != "any") {
                $cmd = "/usr/bin/expect " . ROOT_DIR . "/expect/$router->type/add_dst_acl_entry.expect " . $parameters;
            } elseif ($definition->src_port == "any" && $definition->dst_port == "any") {
                $cmd = "/usr/bin/expect " . ROOT_DIR . "/expect/$router->type/add_any_acl_entry.expect " . $parameters;
            } elseif ($definition->src_port != "any" && $definition->dst_port != "any") {
                $cmd = "/usr/bin/expect " . ROOT_DIR . "/expect/$router->type/add_srcdst_acl_entry.expect " . $parameters;
            }

            exec($cmd, $output, $status);

            if ($status != 0) {
                logToSyslog("ACLMitigation: Expect execution failed ($cmd)", LOG_ERR);
                return false;
            }

            $this->aclmanager->createACLEntry($acl->id, $attack->id, $seq, $output[0]);
        }

        return true;
    }

    protected function _deleteFromRouterACLs($router, $acl, $acl_entry) {
        if (!in_array($router->type, $this->router_types)) {
            logToSyslog("Router type $router->type is not supported", LOG_ERR);
            return false;
        }
        $parameters = "\"$router->mgmt_ip\""
                . " \"$router->username\""
                . " \"$router->password\""
                . " \"$router->enable_password\""
                . " \"$acl_entry->seq\""
                . " \"$acl->name\"";

        $cmd = "/usr/bin/expect " . ROOT_DIR . "/expect/$router->type/del_seq_acl_entry.expect " . $parameters;

        exec($cmd, $output, $status);

        if ($status != 0) {
            logToSyslog("ACLMitigation: Expect execution failed ($cmd)", LOG_ERR);
            return false;
        }

        return true;
    }

    protected function _getAddExpectParameters($acl, $router, $action, $data, $definition, $seq) {
        $parameters = "\"$router->mgmt_ip\""
                . " \"$router->username\""
                . " \"$router->password\""
                . " \"$router->enable_password\""
                . " \"$seq\""
                . " \"$data[4]\""
                . " \"$definition->protocol\""
                . " \"$definition->src_port\""
                . " \"$definition->dst_port\"";

        if ($acl->type == 'outside') {
            $parameters .= " \"$acl->name\"";
            $parameters .= " \"\"";
            $parameters .= " \"$router->outside_vrf\"";
        } elseif ($acl->type == 'protected') {
            $parameters .= " \"\"";
            $parameters .= " \"$acl->name\"";
            $parameters .= " \"$router->protected_vrf\"";
        }

        if (is_array($action->action_parameters)) {
            if (isset($action->action_parameters["full_block"])) {
                $parameters .= " \"$action->action_parameters['full_block']\"";
            } else {
                $parameters .= " \"\"";
            }
        } else {
            $parameters .= " \"\"";
        }

        return $parameters;
    }

}
