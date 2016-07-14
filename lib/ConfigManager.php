<?php

include_once (ROOT_DIR . '/lib/functions/db.php');
include_once (ROOT_DIR . '/lib/models/Subnet.php');
include_once (ROOT_DIR . '/lib/models/Setting.php');

class ConfigManager {

    private $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function getSettingValue($setting) {
        $query = $this->db->prepare("SELECT * FROM ddos_config WHERE setting = :setting");
        $query->bindParam(':setting', $setting);
        $query->execute();

        $row = $query->fetch(PDO::FETCH_ASSOC);
        return $row ['value'];
    }
    
    public function updateSettingValue($setting, $newvalue){
        $query = $this->db->prepare("UPDATE ddos_config SET value = :value WHERE setting = :setting");
        $query->bindParam(':value', $newvalue);
        $query->bindParam(':setting', $setting);
        $query->execute();
        
        return true;
    }
    
    public function addScanSubnet($subnet, $description){
        $query = $this->db->prepare("INSERT INTO ddos_config_subnets (subnet, description) VALUES (:subnet, :description)");
        $query->bindParam(':subnet', $subnet);
        $query->bindParam(':description', $description);
        
        $query->execute();
        
        return true;
    }
    
    public function deleteScanSubnet($subnet){
        $query = $this->db->prepare("DELETE FROM ddos_config_subnets WHERE subnet = :subnet");
        $query->bindParam(':subnet', $subnet);
        
        $query->execute();
        
        return true;
    }
    
    public function deleteScanSubnetById($id){
        $query = $this->db->prepare("DELETE FROM ddos_config_subnets WHERE id = :id");
        $query->bindParam(':id', $id);
        
        $query->execute();
        
        return true;
    }

    public function listSubnets() {
        $query = $this->db->query("SELECT * FROM ddos_config_subnets");
        $subnets = array();
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $subnet = new Subnet($row ['id'], $row ['subnet'], $row ['description']);
            $subnets [] = $subnet;
        }

        return $subnets;
    }
    
    public function listSettings(){
        $query = $this->db->query("SELECT * FROM ddos_config");
        $settings = array();
        
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $setting = new Setting($row ['setting'], $row ['value']);
            $settings [] = $setting;
        }
        
        return $settings;
    }
    
    public function getVersion(){
        $line = fgets(fopen(ROOT_DIR . '/doc/VERSION', 'r'));
        return str_replace("\n", "", $line);
    }

}
