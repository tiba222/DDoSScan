<?php


include_once (ROOT_DIR . '/lib/functions/validator.php');
include_once (ROOT_DIR . '/lib/models/DDoSDefinition.php');
include_once (ROOT_DIR . '/lib/models/DDoSThreshold.php');

class DDoSDefinitionManager {

    protected $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function getDDoSDefinitionById($id) {
        $query = $this->db->prepare("SELECT * FROM ddos_definition WHERE id = :id");
        $query->bindParam(':id', $id);
        $query->execute();

        $row = $query->fetch(PDO::FETCH_ASSOC);
        $definition = new DDoSDefinition($row ['id'], $row ['description'], $row ['protocol'], $row ['src_port'], $row ['dst_port'], $row ['nfdump_filter'], $row ['primary_identifier']);

        return $definition;
    }

    public function listDDoSDefinitions() {
        $query = $this->db->query("SELECT * FROM ddos_definition");

        $definitions = array();
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $definition = new DDoSDefinition($row ['id'], $row ['description'], $row ['protocol'], $row ['src_port'], $row ['dst_port'], $row ['nfdump_filter'], $row ['primary_identifier']);
            $definitions [] = $definition;
        }

        return $definitions;
    }

    public function createDDoSDefinition($description, $protocol, $src_port, $dst_port, $nfdump_filter, $primary_identifier) {
        $query = $this->db->prepare("INSERT INTO ddos_definition (description, protocol, src_port, dst_port, nfdump_filter, primary_identifier) VALUES (:description, :protocol, :src_port, :dst_port, :nfdump_filter, :primary_identifier)");
        $query->bindParam(':description', $description);
        $query->bindParam(':protocol', $protocol);
        $query->bindParam(':src_port', $src_port);
        $query->bindParam(':dst_port', $dst_port);
        $query->bindParam(':nfdump_filter', $nfdump_filter);
        $query->bindParam(':primary_identifier', $primary_identifier);

        $query->execute();

        return $this->getDDoSDefinitionById($this->db->lastInsertId());
    }
    
    public function deleteDDoSDefinitionById($id) {
        $query = $this->db->prepare("DELETE FROM ddos_definition WHERE id = :id");
        $query->bindParam(':id', $id);

        $query->execute();
        
        return true;
    }
    
     public function deleteThresholdById($id) {
        $query = $this->db->prepare("DELETE FROM ddos_attack_threshold WHERE id = :id");
        $query->bindParam(':id', $id);

        $query->execute();
        
        return true;
    }

    public function updateDDoSDefinition($ddos_definition) {
        $query = $this->db->prepare("UPDATE ddos_definition SET description = :description, protocol = :protocol, src_port = :src_port, dst_port = :dst_port, nfdump_filter = :nfdump_filter, primary_identifier = :primary_identifier WHERE id = :id");

        $query->bindParam(':id', $ddos_definition->id);
        $query->bindParam(':description', $ddos_definition->description);
        $query->bindParam(':protocol', $ddos_definition->protocol);
        $query->bindParam(':src_port', $ddos_definition->scr_port);
        $query->bindParam(':dst_port', $ddos_definition->dst_port);
        $query->bindParam(':nfdump_filter', $ddos_definition->nfdump_filter);
        $query->bindParam(':primary_identifier', $ddos_definition->primary_identifier);

        $query->execute();
    }

    public function getDDoSThresholdById($id) {
        $query = $this->db->prepare("SELECT * FROM ddos_attack_threshold WHERE id = :id");
        $query->bindParam(':id', $id);
        $query->execute();

        $row = $query->fetch(PDO::FETCH_ASSOC);
        $threshold = new DDoSThreshold($row ['id'], $row ['ddos_type_id'], $row['priority'], $row ['bps_threshold'], $row ['pps_threshold'], $row ['fps_threshold'], $row ['trend_use'], $row ['trend_window'], $row ['trend_hits']);

        return $threshold;
    }
    
    public function listDDoSThresholds(){
        $query = $this->db->query("SELECT * FROM ddos_attack_threshold");

        $thresholds = array();
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $threshold = new DDoSThreshold($row ['id'], $row ['ddos_type_id'], $row['priority'], $row ['bps_threshold'], $row ['pps_threshold'], $row ['fps_threshold'], $row ['trend_use'], $row ['trend_window'], $row ['trend_hits']);
            $thresholds [] = $threshold;
        }

        return $thresholds;
    }

    public function listDDoSThresholdsByDDoSDefinitionId($ddos_definition_id) {
        $query = $this->db->prepare("SELECT * FROM ddos_attack_threshold WHERE ddos_type_id = :ddos_definition_id ORDER BY priority DESC");
        $query->bindParam(':ddos_definition_id', $ddos_definition_id);
        $query->execute();

        $thresholds = array();
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $threshold = new DDoSThreshold($row ['id'], $row ['ddos_type_id'], $row['priority'], $row ['bps_threshold'], $row ['pps_threshold'], $row ['fps_threshold'], $row ['trend_use'], $row ['trend_window'], $row ['trend_hits']);
            $thresholds [] = $threshold;
        }

        return $thresholds;
    }

    public function createDDoSThreshold($ddos_type_id, $priority, $bps_threshold, $pps_threshold, $fps_threshold, $trend_use, $trend_window, $trend_hits) {
        $query = $this->db->prepare("INSERT INTO ddos_attack_threshold (ddos_type_id, priority, bps_threshold, pps_threshold, fps_threshold, trend_use, trend_window, trend_hits ) VALUES (:ddos_type_id, :priority, :bps_threshold, :pps_threshold, :fps_threshold, :trend_use, :trend_window, :trend_hits)");
        $query->bindParam(':ddos_type_id', $ddos_type_id);
        $query->bindParam(':priority', $priority);
        $query->bindParam(':bps_threshold', $bps_threshold);
        $query->bindParam(':pps_threshold', $pps_threshold);
        $query->bindParam(':fps_threshold', $fps_threshold);
        $query->bindParam(':trend_use', $trend_use);
        $query->bindParam(':trend_window', $trend_window);
        $query->bindParam(':trend_hits', $trend_hits);

        $query->execute();

        return $this->getDDoSThresholdById($this->db->lastInsertId());
    }

    public function updateDDoSThreshold($ddos_threshold) {
        $query = $this->db->prepare("UPDATE ddos_attack_threshold SET ddos_type_id = :ddos_type_id, priority = :priority, bps_threshold = :bps_threshold, pps_threshold = :pps_threshold, fps_threshold = :fps_threshold, trend_use = :trend_use, trend_window = :trend_window, trend_hits = :trend_hits WHERE id = :id");

        $query->bindParam(':id', $ddos_threshold->id);
        $query->bindParam(':ddos_type_id', $ddos_threshold->ddos_type_id);
        $query->bindParam(':priority', $ddos_threshold->priority);
        $query->bindParam(':bps_threshold', $ddos_threshold->bps_threshold);
        $query->bindParam(':pps_threshold', $ddos_threshold->pps_threshold);
        $query->bindParam(':fps_threshold', $ddos_threshold->fps_threshold);
        $query->bindParam(':trend_use', $ddos_threshold->trend_use);
        $query->bindParam(':trend_window', $ddos_threshold->trend_window);
        $query->bindParam(':trend_hits', $ddos_threshold->trend_hits);

        $query->execute();
    }

}
