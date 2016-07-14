<?php

include_once (ROOT_DIR . '/lib/functions/db.php');
include_once (ROOT_DIR . '/lib/functions/log.php');
include_once (ROOT_DIR . '/lib/models/DDoSAttack.php');
include_once (ROOT_DIR . '/lib/models/DDoSAttackEntry.php');

class DDoSAttackManager {

    protected $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function getDDoSAttackById($id) {
        $query = $this->db->prepare("SELECT * FROM ddos_attack WHERE id = :id");
        $query->bindParam(':id', $id);
        $query->execute();

        $row = $query->fetch(PDO::FETCH_ASSOC);
        $attack = new DDoSAttack($row ['id'], $row ['ddos_type_id'], $row ['time_start'], $row ['time_last_traffic'], $row ['target_ip'], $row['active']);

        return $attack;
    }

    public function listDDoSAttacks() {
        $query = $this->db->query("SELECT * FROM ddos_attack");

        $attacks = array();
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $attack = new DDosAttack($row ['id'], $row ['ddos_type_id'], $row ['time_start'], $row ['time_last_traffic'], $row ['target_ip'], $row['active']);
            $attacks [] = $attack;
        }

        return $attacks;
    }
    
    public function listActiveDDoSAttacks() {
        $query = $this->db->query("SELECT * FROM ddos_attack WHERE active = 1");

        $attacks = array();
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $attack = new DDosAttack($row ['id'], $row ['ddos_type_id'], $row ['time_start'], $row ['time_last_traffic'], $row ['target_ip'], $row['active']);
            $attacks [] = $attack;
        }

        return $attacks;
    }

    public function listDDoSAttacksByTargetIP($target) {
        $query = $this->db->prepare("SELECT * FROM ddos_attack WHERE target_ip = :target");
        $query->bindParam(':target', $target);
        $query->execute();

        $attacks = array();
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $attack = new DDosAttack($row ['id'], $row ['ddos_type_id'], $row ['time_start'], $row ['time_last_traffic'], $row ['target_ip'], $row['active']);
            $attacks [] = $attack;
        }

        return $attacks;
    }

    public function createDDoSAttack($ddos_type_id, $time_start, $time_last_traffic, $target_ip) {
        $query = $this->db->prepare("INSERT INTO ddos_attack (ddos_type_id, time_start, time_last_traffic, target_ip, active) VALUES (:ddos_type_id, :time_start, :time_last_traffic, :target_ip, 1)");
        $query->bindParam(':ddos_type_id', $ddos_type_id);
        $query->bindParam(':time_start', $time_start);
        $query->bindParam(':time_last_traffic', $time_last_traffic);
        $query->bindParam(':target_ip', $target_ip);

        $query->execute();

        return $this->getDDoSAttackById($this->db->lastInsertId());
    }

    public function getActiveDDoSAttackByInterval($target, $ddos_type_id, $interval) {
        $query = $this->db->prepare("SELECT * FROM ddos_attack WHERE target_ip = :target AND ddos_type_id = :ddos_type_id AND time_last_traffic >= DATE_SUB(NOW(), INTERVAL :interval MINUTE)");
        $query->bindParam(':target', $target);
        $query->bindParam(':ddos_type_id', $ddos_type_id);
        $query->bindParam(':interval', $interval);
        $query->execute();

        $row = $query->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $attack = new DDoSAttack($row ['id'], $row ['ddos_type_id'], $row ['time_start'], $row ['time_last_traffic'], $row ['target_ip'], $row['active']);

            return $attack;
        }

        return false;
    }

    public function updateDDoSAttack($ddos_attack) {
        $query = $this->db->prepare("UPDATE ddos_attack SET ddos_type_id = :ddos_type_id, time_start = :time_start, time_last_traffic = :time_last_traffic, target_ip = :target_ip, active = :active WHERE id = :id");

        $query->bindParam(':id', $ddos_attack->id);
        $query->bindParam(':ddos_type_id', $ddos_attack->ddos_type_id);
        $query->bindParam(':time_start', $ddos_attack->time_start);
        $query->bindParam(':time_last_traffic', $ddos_attack->time_last_traffic);
        $query->bindParam(':target_ip', $ddos_attack->target_ip);
        $query->bindParam(':active', $ddos_attack->active);

        $query->execute();
    }

    public function getDDoSAttackEntryById($id) {
        $query = $this->db->prepare("SELECT * FROM ddos_attack_entry WHERE id = :id");
        $query->bindParam(':id', $id);
        $query->execute();

        $row = $query->fetch(PDO::FETCH_ASSOC);
        $entry = new DDosAttackEntry($row ['id'], $row ['ddos_attack_id'], $row ['timestamp'], $row ['bps'], $row ['pps'], $row ['fps']);

        return $entry;
    }

    public function DDoSAttackEntryExists($timestamp, $ddos_attack_id) {
        $query = $this->db->prepare("SELECT * FROM ddos_attack_entry WHERE timestamp = :timestamp AND ddos_attack_id = :ddos_attack_id");
        $query->bindParam(':timestamp', $timestamp);
        $query->bindParam('ddos_attack_id', $ddos_attack_id);
        $query->execute();

        $row = $query->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return true;
        }
        return false;
    }

    public function listDDoSAttackEntriesByDDoSAttackId($id) {
        $query = $this->db->prepare("SELECT * FROM ddos_attack_entry WHERE ddos_attack_id = :id");
        $query->bindParam(':id', $id);
        $query->execute();

        $entries = array();
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $entry = new DDosAttackEntry($row ['id'], $row ['ddos_attack_id'], $row ['timestamp'], $row ['bps'], $row ['pps'], $row ['fps']);
            $entries [] = $entry;
        }

        return $entries;
    }

    public function listDDoSAttackEntriesInWindow($target, $ddos_type_id, $interval) {
        $query = $this->db->prepare("SELECT * FROM ddos_attack_entry LEFT JOIN ddos_attack ON ddos_attack_entry.ddos_attack_id = ddos_attack.id WHERE ddos_attack.target_ip = :target AND ddos_attack.ddos_type_id = :ddos_type_id AND ddos_attack_entry.timestamp >= DATE_SUB(NOW(), INTERVAL :interval HOUR)");
        $query->bindParam(':target', $target);
        $query->bindParam(':ddos_type_id', $ddos_type_id);
        $query->bindParam(':interval', $interval);
        $query->execute();

        $entries = array();
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $entry = new DDosAttackEntry($row ['id'], $row ['ddos_attack_id'], $row ['timestamp'], $row ['bps'], $row ['pps'], $row ['fps']);
            $entries [] = $entry;
        }

        return $entries;
    }

    public function createDDoSAttackEntry($ddos_attack_id, $timestamp, $bps, $pps, $fps) {
        if (!$this->DDoSAttackEntryExists($timestamp, $ddos_attack_id)) {
            $query = $this->db->prepare("INSERT INTO ddos_attack_entry (ddos_attack_id, timestamp, bps, pps, fps) VALUES (:ddos_attack_id, :timestamp, :bps, :pps, :fps)");
            $query->bindParam(':ddos_attack_id', $ddos_attack_id);
            $query->bindParam(':timestamp', $timestamp);
            $query->bindParam(':bps', $bps);
            $query->bindParam(':pps', $pps);
            $query->bindParam(':fps', $fps);

            $query->execute();

            return $this->getDDoSAttackEntryById($this->db->lastInsertId());
        } else {
            logToSyslog("The same DDoS Attack entry already exists, not creating again", LOG_ERR);
            return false;
        }
    }

    public function updateDDoSAttackEntry($ddos_attack_entry) {
        $query = $this->db->prepare("UPDATE ddos_attack_entry SET ddos_attack_id = :ddos_attack_id, timestamp = :timestamp, bps = :bps, pps = :pps, fps = :fps WHERE id = :id");

        $query->bindParam(':id', $ddos_attack_entry->id);
        $query->bindParam(':ddos_attack_id', $ddos_attack_entry->ddos_attack_id);
        $query->bindParam(':timestamp', $ddos_attack_entry->timestamp);
        $query->bindParam(':bps', $ddos_attack_entry->bps);
        $query->bindParam(':pps', $ddos_attack_entry->pps);
        $query->bindParam(':fps', $ddos_attack_entry->fps);

        $query->execute();
    }

    public function updateAttackStatuses($active_attacks) {
        $query = $this->db->query("SELECT * FROM ddos_attack WHERE active = 1");
        $query->execute();

        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $attack = $this->getDDoSAttackById($row['id']);

            $found = false;
            for ($i = 0; $i < sizeof($active_attacks); $i++) {
                if ($row['id'] == $active_attacks[$i]->id) {
                    $found = true;
                    unset($active_attacks[$i]);
                }
            }

            if (!$found) {
                $attack->active = 0;
                $this->updateDDoSAttack($attack);
            }
        }
        foreach ($active_attacks as $reactivate) {
            $reactivate->active = 1;
            $this->updateDDoSAttack($reactivate);
        }
    }

}
