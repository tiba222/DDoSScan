<?php

include_once (ROOT_DIR . '/lib/functions/db.php');
include_once (ROOT_DIR . '/lib/ConfigManager.php');
include_once (ROOT_DIR . '/lib/DDoSAttackManager.php');
include_once (ROOT_DIR . '/lib/models/DDoSAttack.php');
include_once (ROOT_DIR . '/lib/functions/log.php');

class MitigationManager {

    protected $db;
    protected $configmanager;
    protected $ddosattackmanager;

    public function __construct() {
        $this->db = getDB();
        $this->configmanager = new ConfigManager();
        $this->ddosattackmanager = new DDoSAttackManager();
    }

    public function addMitigationHistory($attack, $autoremove, $action_parameters, $reason) {
        $days = $this->_getAutoRemoveDays($action_parameters);

        $query = $this->db->prepare("INSERT INTO ddos_mitigation_history (ddos_attack_id, mitigated_at, last_traffic, autoremove, autoremove_days, reason, active) VALUES (:ddos_attack_id, NOW(), NOW(), :autoremove, :autoremove_days, :reason, 1)");
        $query->bindParam(':ddos_attack_id', $attack->id);
        $query->bindParam(':autoremove', $autoremove);
        $query->bindParam(':autoremove_days', $days);
        $query->bindParam(':reason', $reason);

        $query->execute();
    }

    public function setInactive($id) {
        $query = $this->db->prepare("UPDATE ddos_mitigation_history SET active = 0 WHERE id = :id");
        $query->bindParam(':id', $id);

        $query->execute();
    }

    public function checkForActiveMitigation($attack) {
        $query = $this->db->query("SELECT id, ddos_attack_id FROM ddos_mitigation_history WHERE active = TRUE");

        while ($mitigation = $query->fetch(PDO::FETCH_ASSOC)) {
            $mitigated = $this->ddosattackmanager->getDDoSAttackById($mitigation['ddos_attack_id']);

            if ($mitigated->target_ip == $attack->target_ip && $mitigated->ddos_type_id == $attack->ddos_type_id) {
                return $mitigation['id'];
            }
        }

        return false;
    }

    public function updateMitigationHistory($id, $action_parameters) {
        $update = $this->db->prepare("UPDATE ddos_mitigation_history SET last_traffic = NOW() WHERE id = :id");
        $update->bindValue(':id', $id);
        $update->execute();

        $select = $this->db->prepare("SELECT * FROM ddos_mitigation_history WHERE id = :id");
        $select->bindParam(':id', $id);
        $select->execute();

        $row = $select->fetch(PDO::FETCH_ASSOC);

        $days = $this->_getAutoRemoveDays($action_parameters);
        if ($days > $row['autoremove_days']) {
            $update_days = $this->db->prepare("UPDATE ddos_mitigation_history SET autoremove_days = :autoremove_days");
            $update_days->bindParam(':autoremove_days', $days);
            $update_days->execute();

            logToSyslog("ACLMitigation: Action has mitigation for $days days configured which is more than previously stored for this mitigation, updated in history", LOG_INFO);
        }
    }

    public function listExpiredMitigations() {
        $query = $this->db->prepare("SELECT * FROM ddos_mitigation_history WHERE last_traffic <= DATE_SUB(NOW(), INTERVAL autoremove_days DAY) AND active = 1;");
        $query->execute();

        $mitigations = array();
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $mitigations [] = $row;
        }

        return $mitigations;
    }

    protected function _getAutoRemoveDays($action_parameters) {
        $days = $this->configmanager->getSettingValue("def_autoremove_days");
        if (is_array($action_parameters)) {
            if (isset($action_parameters["autoremove_days"])) {
                $days = $action_parameters["autoremove_days"];
            }
        }
        return $days;
    }

}
