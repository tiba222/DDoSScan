<?php

interface ActionHandler {

    // This function is called before the actual scan starts
    public function preScan();
    
    // This function is called for each DDoS detected
    public function onDDoS($action, $data, $definition, $attack);

    // This function is called afther the scan
    public function postScan();
}
