<?php

include_once (ROOT_DIR . '/lib/ConfigManager.php');

class CLIConfig {

    protected $cfg;

    public function __construct() {
        $this->cfg = new ConfigManager();
    }

    public function addSubnet($argv) {
        if ($this->cfg->addScanSubnet($argv[3], $argv[4])) {
            echo("OK\n");
        }
    }
    
    public function deleteSubnet($argv) {
        if ($this->cfg->deleteScanSubnet($argv[3])) {
            echo("OK\n");
        }
    }
    
    public function changeSetting($argv){
        if($this->cfg->updateSettingValue($argv[3], $argv[4])){
            echo("OK\n");
        }
    }
    
    public function showConfig($argv){
        foreach($this->cfg->listSettings() as $setting){
            echo("$setting->name: $setting->value\n");
        }
        
        echo("\nSubnets to scan:\n");
        
        foreach($this->cfg->listSubnets() as $subnet){
            echo("$subnet->subnet ($subnet->description)\n");
        }
    }

}
