<?php

include_once (ROOT_DIR . '/lib/functions/db.php');
include_once (ROOT_DIR . '/lib/models/ACL.php');
include_once (ROOT_DIR . '/lib/models/ACLEntry.php');

class ACLManager {

    protected $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function listACLs() {
        $query = $this->db->query("SELECT * from ddos_acl");

        $acls = array();
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $acl = new ACL($row ['id'], $row ['router_id'], $row ['name'], $row ['type'], $row ['seq_start'], $row ['seq_end']);
            $acls [] = $acl;
        }

        return $acls;
    }

    public function listACLsByRouterId($router_id) {
        $query = $this->db->prepare("SELECT * from ddos_acl WHERE router_id = :router_id");
        $query->execute(array(
            'router_id' => $router_id
        ));

        $acls = array();
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $acl = new ACL($row ['id'], $row ['router_id'], $row ['name'], $row ['type'], $row ['seq_start'], $row ['seq_end']);
            $acls [] = $acl;
        }

        return $acls;
    }

    public function getACLById($id) {
        $query = $this->db->prepare("SELECT * FROM ddos_acl WHERE id = :id");
        $query->bindParam(':id', $id);
        $query->execute();

        $row = $query->fetch(PDO::FETCH_ASSOC);
        $acl = new ACL($row ['id'], $row ['router_id'], $row ['name'], $row ['type'], $row ['seq_start'], $row ['seq_end']);

        return $acl;
    }

    public function createACL($router_id, $name, $type, $seq_start, $seq_end) {
        $query = $this->db->prepare("INSERT INTO ddos_acl (router_id, name, type, seq_start, seq_end) VALUES (:router_id, :name, :type, :seq_start, :seq_end)");
        $query->bindParam(':router_id', $router_id);
        $query->bindParam(':name', $name);
        $query->bindParam(':acl_type', $type);
        $query->bindParam(':seq_start', $seq_start);
        $query->bindParam(':seq_end', $seq_end);

        $query->execute();

        return $this->getACLById($this->db->lastInsertId());
    }

    public function updateACL($acl) {
        $query = $this->db->prepare("UPDATE ddos_acl SET router_id = :router_id, type = :type, name = :name, seq_start = :seq_start, seq_end = :seq_end WHERE id = :id");

        $query->bindParam(':id', $acl->id);
        $query->bindParam(':router_id', $acl->router_id);
        $query->bindParam(':name', $acl->name);
        $query->bindParam(':type', $acl->type);
        $query->bindParam(':seq_start', $acl->seq_start);
        $query->bindParam(':seq_end', $acl->seq_end);

        $query->execute();
    }

    public function listACLEntrysByACLId($acl_id) {
        $query = $this->db->prepare("SELECT * from ddos_acl_entry WHERE acl_id = :acl_id");
        $query->execute(array(
            'acl_id' => $acl_id
        ));

        $entries = array();
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $entry = new ACLEntry($row ['id'], $row ['acl_id'], $row ['ddos_attack_id'], $row ['seq'], $row ['content']);
            $entries [] = $entry;
        }

        return $entries;
    }

    public function listACLEntriesByDDoSAttackId($ddos_attack_id) {
        $query = $this->db->prepare("SELECT * from ddos_acl_entry WHERE ddos_attack_id = :ddos_attack_id");
        $query->execute(array(
            'ddos_attack_id' => $ddos_attack_id
        ));

        $entries = array();
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $entry = new ACLEntry($row ['id'], $row ['acl_id'], $row ['ddos_attack_id'], $row ['seq'], $row ['content']);
            $entries [] = $entry;
        }

        return $entries;
    }

    public function getACLEntryById($id) {
        $query = $this->db->prepare("SELECT * FROM ddos_acl_entry WHERE id = :id");
        $query->bindParam(':id', $id);
        $query->execute();

        $row = $query->fetch(PDO::FETCH_ASSOC);
        $entry = new ACLEntry($row ['id'], $row ['acl_id'], $row ['ddos_attack_id'], $row ['seq'], $row ['content']);

        return $entry;
    }

    public function deleteACLEntryById($id) {
        $query = $this->db->prepare("DELETE FROM ddos_acl_entry WHERE id = :id");
        $query->bindParam(':id', $id);

        $query->execute();
    }

    public function createACLEntry($acl_id, $ddos_attack_id, $seq, $content) {
        $query = $this->db->prepare("INSERT INTO ddos_acl_entry (acl_id, ddos_attack_id, seq, content) VALUES (:acl_id, :ddos_attack_id, :seq, :content)");
        $query->bindParam(':acl_id', $acl_id);
        $query->bindParam(':ddos_attack_id', $ddos_attack_id);
        $query->bindParam(':seq', $seq);
        $query->bindParam(':content', $content);

        $query->execute();

        return $this->getACLEntryById($this->db->lastInsertId());
    }

    public function updateACLEntry($acl_entry) {
        $query = $this->db->prepare("UPDATE ddos_acl_entry SET acl_id = :acl_id, ddos_attack_id = :ddos_attack_id, seq = :seq, content = :content WHERE id = :id");

        $query->bindParam(':id', $acl_entry->id);
        $query->bindParam(':acl_id', $acl_entry->acl_id);
        $query->bindParam(':ddos_attack_id', $acl_entry->ddos_attack_id);
        $query->bindParam(':seq', $acl_entry->seq);
        $query->bindParam(':content', $acl_entry->content);

        $query->execute();
    }

    public function getFreeSeqNumber($acl) {
        for ($i = $acl->seq_start; $i < $acl->seq_end; $i++) {
            $query = $this->db->prepare("SELECT count(*) AS count FROM ddos_acl_entry WHERE seq = :seq");
            $query->bindParam(':seq', $i);
            $query->execute();

            $row = $query->fetch(PDO::FETCH_ASSOC);

            if ($row['count'] == 0) {

                return $i;
            }
        }
        
        return -1;
    }

}
