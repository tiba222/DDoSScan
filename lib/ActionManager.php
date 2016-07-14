<?php

include_once (ROOT_DIR . '/lib/functions/db.php');
include_once (ROOT_DIR . '/lib/functions/log.php');
include_once (ROOT_DIR . '/lib/functions/ipv4.php');
include_once (ROOT_DIR . '/lib/models/Action.php');
include_once (ROOT_DIR . '/lib/models/ActionExclusion.php');
include_once (ROOT_DIR . '/lib/models/DDoSAttack.php');
include_once (ROOT_DIR . '/lib/handlers/EmailAlert.php');
include_once (ROOT_DIR . '/lib/handlers/ACLMitigation.php');

class ActionManager {

    protected $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function execActionPreScan() {
        logToSyslog("Executing pre-scan hooks", LOG_INFO);

        foreach ($this->listActions() as $action) {
            $handler = $this->_getActionHandler($action);
            if ($handler) {
                $handler->preScan();
            }
        }
    }

    public function execActionOnDDoS($attack, $threshold, $data, $definition) {
        foreach ($this->listActionsByThresholdId($threshold->id) as $action) {
            logToSyslog("Processing action $action->description for attack towards $data[4]", LOG_INFO);

            if ($this->_checkActionHistory($attack, $action) && $action->once == 1) {
                logToSyslog("Action $action->action was already executed for this DDoS attack and action is configured to run only once, not executing again", LOG_INFO);
                continue;
            }

            if ($this->_checkActionIsEcluded($data[4], $action)) {
                continue;
            }

            $handler = $this->_getActionHandler($action);
            if ($handler) {
                $retv = $handler->onDDoS($action, $data, $definition, $attack);
            }

            if ($retv) {
                $this->_addActionHistory($attack, $action);
            } else {
                logToSyslog("Processing action $action->description failed for attack towards $data[4]", LOG_ERR);
            }
        }
    }

    public function execActionPostScan() {
        logToSyslog("Executing post-scan hooks", LOG_INFO);

        foreach ($this->listActions() as $action) {
            $handler = $this->_getActionHandler($action);
            if ($handler) {
                $handler->postScan();
            }
        }
    }

    public function createAction($description, $action, $action_parameters, $once) {
        $query = $this->db->prepare("INSERT INTO ddos_action (description, action, action_parameters, once) VALUES (:description, :action, :action_parameters, :once)");
        $query->bindParam(':description', $description);
        $query->bindParam(':action', $action);
        $query->bindParam(':action_parameters', json_encode($action_parameters));
        $query->bindParam(':once', $once);

        $query->execute();

        return $this->getActionById($this->db->lastInsertId());
    }

    public function deleteActionById($id) {
        $query = $this->db->prepare("DELETE FROM ddos_action WHERE id = :id");
        $query->bindParam(':id', $id);

        $query->execute();
    }

    public function listActions() {
        $query = $this->db->query("SELECT * FROM ddos_action");

        $actions = array();
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $action = new Action($row ['id'], $row['description'], $row ['action'], json_decode($row ['action_parameters'], TRUE), $row['once']);
            $actions [] = $action;
        }

        return $actions;
    }

    public function listActionsByThresholdId($threshold_id) {
        $query = $this->db->prepare("SELECT * FROM ddos_threshold_action RIGHT JOIN ddos_action ON ddos_threshold_action.action_id = ddos_action.id WHERE ddos_threshold_action.threshold_id = :threshold_id");
        $query->bindParam(':threshold_id', $threshold_id);
        $query->execute();

        $actions = array();
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $action = new Action($row ['id'], $row['description'], $row ['action'], json_decode($row ['action_parameters'], TRUE), $row['once']);
            $actions [] = $action;
        }

        return $actions;
    }

    public function linkActionToThreshold($action_id, $threshold_id) {
        $query = $this->db->prepare("INSERT INTO ddos_threshold_action (action_id, threshold_id) VALUES (:action_id, :threshold_id)");
        $query->bindParam('action_id', $action_id);
        $query->bindParam('threshold_id', $threshold_id);
        $query->execute();

        return true;
    }

    public function unlinkActionFromThreshold($action_id, $threshold_id) {
        $query = $this->db->prepare("DELETE FROM ddos_threshold_action WHERE action_id = :action_id AND threshold_id = :threshold_id");
        $query->bindParam('action_id', $action_id);
        $query->bindParam('threshold_id', $threshold_id);
        $query->execute();

        if ($query->rowCount() > 0) {
            return true;
        }

        return false;
    }

    public function getActionUseCount($id) {
        $query = $this->db->prepare("SELECT COUNT(*) as count from ddos_threshold_action where action_id = :action_id");
        $query->bindParam('action_id', $id);
        $query->execute();

        return $query->fetch(PDO::FETCH_ASSOC)['count'];
    }

    public function getActionById($id) {
        $query = $this->db->prepare("SELECT * FROM ddos_action WHERE id = :id");
        $query->bindParam(':id', $id);
        $query->execute();

        $row = $query->fetch(PDO::FETCH_ASSOC);
        $action = new Action($row ['id'], $row['description'], $row ['action'], json_decode($row ['action_parameters'], TRUE), $row['once']);

        return $action;
    }

    public function updateAction($action) {
        $query = $this->db->prepare("UPDATE ddos_action SET description = :description, action = :action, action_parameters = :action_parameters, once = :once WHERE id = :id");

        $query->bindParam(':id', $action->id);
        $query->bindParam(':description', $action->description);
        $query->bindParam(':action', $action->action);
        $query->bindParam(':action_parameters', json_encode($action->action_parameters));
        $query->bindParam(':once', $action->once);

        $query->execute();
    }

    public function createExcludedAction($target, $excluded_action) {
        $query = $this->db->prepare("INSERT INTO ddos_ip_exclusions (target, excluded_action) VALUES (:target, :excluded_action)");
        $query->bindParam(':target', $target);
        $query->bindParam('excluded_action', $excluded_action);

        $query->execute();

        return $this->getExcludedActionById($this->db->lastInsertId());
    }

    public function deleteExcludedActionById($id) {
        $query = $this->db->prepare("DELETE FROM ddos_ip_exclusions WHERE id = :id");
        $query->bindParam(':id', $id);

        $query->execute();
    }

    public function getExcludedActionById($id) {
        $query = $this->db->prepare("SELECT * FROM ddos_ip_exclusions WHERE id = :id");
        $query->bindParam(':id', $id);
        $query->execute();

        $row = $query->fetch(PDO::FETCH_ASSOC);
        $excluded = new ActionExlusion($row ['id'], $row['target'], $row ['excluded_action']);

        return $excluded;
    }

    public function updateExcludedAction($excluded) {
        $query = $this->db->prepare("UPDATE ddos_ip_exclusions SET target = :target, excluded_action = :excluded_action WHERE id = :id");

        $query->bindParam(':id', $excluded->id);
        $query->bindParam(':target', $excluded->target);
        $query->bindParam(':excluded_action', $excluded->excluded_action);

        $query->execute();
    }

    public function listExcludedActions() {
        $query = $this->db->query("SELECT * FROM ddos_ip_exclusions");

        $exclusions = array();
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $excluded = new ActionExlusion($row ['id'], $row['target'], $row ['excluded_action']);
            $exclusions [] = $excluded;
        }

        return $exclusions;
    }

    protected function _getActionHandler($action) {
        switch ($action->action) {
            case 'mitigate_acl':
                $obj = new ACLMitigation();
                break;
            case 'alert_email':
                $obj = new EmailAlert();
                break;
            default :
                logToSyslog("Action $action->action unknown, skipping", LOG_ERR);
                return false;
        }

        return $obj;
    }

    protected function _checkActionHistory($attack, $action) {
        $query = $this->db->prepare("SELECT * FROM ddos_action_history WHERE ddos_attack_id = :ddos_attack_id AND action_id = :action_id");
        $query->bindParam(':ddos_attack_id', $attack->id);
        $query->bindParam(':action_id', $action->id);

        $query->execute();
        $rows = $query->fetch(PDO::FETCH_ASSOC);

        if ($rows) {
            return true;
        }
    }

    protected function _addActionHistory($attack, $action) {
        $insert = $this->db->prepare("INSERT INTO ddos_action_history (ddos_attack_id, action_id, executed_at) VALUES (:ddos_attack_id, :action_id, NOW())");
        $insert->bindParam(':ddos_attack_id', $attack->id);
        $insert->bindParam(':action_id', $action->id);

        $insert->execute();
    }

    protected function _checkActionIsEcluded($ip, $action) {
        $query = $this->db->prepare("SELECT * FROM ddos_ip_exclusions WHERE excluded_action = :excluded_action");
        $query->bindParam(':excluded_action', $action->action);

        $query->execute();

        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            if (belongsToSubnet($ip, $row['target'])) {
                return true;
            }
        }
        return false;
    }

}
