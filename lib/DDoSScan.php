<?php

include_once (ROOT_DIR . '/lib/DDoSDefinitionManager.php');
include_once (ROOT_DIR . '/lib/DDoSAttackManager.php');
include_once (ROOT_DIR . '/lib/ConfigManager.php');
include_once (ROOT_DIR . '/lib/ActionManager.php');
include_once (ROOT_DIR . '/lib/NFDump.php');
include_once (ROOT_DIR . '/lib/functions/log.php');
include_once (ROOT_DIR . '/lib/functions/db.php');

class DDoSScan {

    protected $defintionmanager;
    protected $ddosattackmanager;
    protected $actionmanager;
    protected $configmanager;
    protected $nfdump;
    protected $active_attacks;

    public function __construct() {
        $this->defintionmanager = new DDoSDefinitionManager();
        $this->ddosattackmanager = new DDoSAttackManager();
        $this->actionmanager = new ActionManager();
        $this->configmanager = new ConfigManager();
        $this->nfdump = new NFDump();

        $this->active_attacks = array();
    }

    public function run() {
        $version = $this->configmanager->getVersion();
        logToSyslog("Starting run of DDoSScan (version: $version)", LOG_INFO);
        
        $this->actionmanager->execActionPreScan();

        logToSyslog("Waiting " . $this->configmanager->getSettingValue('scan_delay') . " seconds for all flow data to be available", LOG_INFO);
        sleep($this->configmanager->getSettingValue('scan_delay'));

        foreach ($this->defintionmanager->listDDoSDefinitions() as $definition) {
            logToSyslog("Scanning for definition: $definition->description (Filter: $definition->nfdump_filter)", LOG_DEBUG);
            foreach ($this->nfdump->query($definition) as $data) {
                $this->getBestMatchingThreshold($definition, $data);
            }
        }
        
        $this->ddosattackmanager->updateAttackStatuses($this->active_attacks);
        
        $this->actionmanager->execActionPostScan();

        logToSyslog("Finished run of DDoSScan", LOG_INFO);
    }

    private function getBestMatchingThreshold($definition, $data) {
        $best_threshold = false;

        foreach ($this->defintionmanager->listDDoSThresholdsByDDoSDefinitionId($definition->id) as $threshold) {
            $attack = $this->matchesThreshold($data, $threshold, $definition);
            if ($attack) {
                $best_threshold = $threshold;
                $m_attack = $attack;
            }
        }

        if ($best_threshold) {
            logToSyslog("Best matching threshold for $definition->description towards $data[4]: bps = $threshold->bps_threshold, pps => $threshold->pps_threshold, fps = $threshold->fps_threshold, using trends = $threshold->trend_use, trend window = $threshold->trend_window, trend hits = $threshold->trend_hits", LOG_INFO);
            $this->actionmanager->execActionOnDDoS($m_attack, $best_threshold, $data, $definition);
        }

        return $best_threshold;
    }

    private function matchesThreshold($data, $threshold, $definition) {
        if ((($threshold->bps_threshold != - 1) && ($data [12] > $threshold->bps_threshold)) || (($threshold->pps_threshold != - 1) && ($data [11] > $threshold->pps_threshold)) || (($threshold->fps_threshold != - 1) && ($data [5] > $threshold->fps_threshold))) {
            logToSyslog("Possible match found for $definition->description towards $data[4]", LOG_INFO);
            $attack = $this->storeDDoSAttack($data, $definition);
            if ($threshold->trend_use == 1) {
                logToSyslog("Threshold is using trends, checking if trend criteria are matched", LOG_INFO);
                return $this->matchesTrendThreshold($data, $threshold, $definition, $attack);
            } else {
                logToSyslog("Threshold is not using trends, threshold matched", LOG_INFO);
                return $attack;
            }
        }
        return false;
    }

    private function matchesTrendThreshold($data, $threshold, $definition, $attack) {
        if (count($this->ddosattackmanager->listDDoSAttackEntriesInWindow($data[4], $threshold->ddos_type_id, $threshold->trend_window)) >= $threshold->trend_hits) {
            logToSyslog("Threshold trend criteria matched for DDoS attack ($definition->description) towards $data[4]", LOG_INFO);
            return $attack;
        }

        logToSyslog("Threshold trend criteria not matched for DDoS attack ($definition->description) towards $data[4]", LOG_INFO);
        return false;
    }

    private function storeDDoSAttack($data, $definition) {
        $now = date('Y-m-d H:i:s');
        $attack = $this->ddosattackmanager->getActiveDDoSAttackByInterval($data [4], $definition->id, $this->configmanager->getSettingValue('ddos_interval'));
        if (!$attack) {
            $attack = $this->ddosattackmanager->createDDoSAttack($definition->id, $now, $now, $data [4]);
            $this->ddosattackmanager->createDDoSAttackEntry($attack->id, $data[0], $data [12], $data [11], $data [5]);
        } else {
            if ($this->ddosattackmanager->createDDoSAttackEntry($attack->id, $data[0], $data [12], $data [11], $data [5])) {

                $attack->time_last_traffic = $now;
                $this->ddosattackmanager->updateDDoSAttack($attack);
            }
        }

        $this->active_attacks[] = $attack;

        return $attack;
    }

}
