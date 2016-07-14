<?php

include_once (ROOT_DIR . '/lib/ConfigManager.php');
include_once (ROOT_DIR . '/lib/models/DDoSDefinition.php');
include_once (ROOT_DIR . '/config.php');

class NFDump {

    protected $configmanager;

    public function __construct() {
        $this->configmanager = new ConfigManager ();
    }

    public function query($ddos_defintion) {
        $query = $this->configmanager->getSettingValue('nfdump_location') .
                ' -M ' . $this->configmanager->getSettingValue('nfsen_datadir') .
                $this->getNFDumpDataSources() .
                ' -T -r '  . $this->getLatestDataFilename() .
                ' -n ' . $this->configmanager->getSettingValue('scan_top_n') .
                ' -s dstip/' . $ddos_defintion->primary_identifier . ' -o csv -q "' .
                $this->getNFDumpFilterWithSubnets($ddos_defintion->nfdump_filter) . '"';

        $netflow_data = shell_exec($query);

        $temp = fopen('php://temp', 'r+');
        fwrite($temp, $netflow_data);
        rewind($temp);

        $multiplier = $this->configmanager->getSettingValue('netflow_sampling');
        $result = array();

        $i = 0;
        while (($data = fgetcsv($temp)) !== FALSE) {
            if ($i > 0 && !$data[0] == "") {
                $data[12] = $data[12] * $multiplier;
                $data[11] = $data[11] * $multiplier;
                $data[5] = $data[5] * $multiplier;

                $result [] = $data;
            }
            $i++;
        }

        return $result;
    }

    private function getLatestDataFilename() {
        $filenames = str_replace("\n", "", shell_exec('find ' . $this->configmanager->getSettingValue('nfsen_datadir') . ' -mindepth 4 -type f -printf \'%T@ %p\0\n\' | sort -zk 1nr | cut -d " " -f 2 | tail -1 | cut -d / -f 7-'));
        return escapeshellarg($filenames);
    }

    private function getNFDumpFilterWithSubnets($nfdump_filter) {
        $filter = $nfdump_filter . ' && (';
        $subnets = $this->configmanager->listSubnets();

        if (count($subnets) < 1) {
            logToSyslog("You need to configure at least one destination subnet to check for DDoS attacks, aborting", LOG_ERR);
            die();
        }

        for ($i = 0; $i < count($subnets); $i ++) {
            $filter .= 'dst net ' . $subnets[$i]->subnet;

            if ($i != (count($subnets) - 1)) {
                $filter .= ' || ';
            }
        }

        $filter .= ')';

        return $filter;
    }

    private function getNFDumpDataSources() {
        $dirs = glob($this->configmanager->getSettingValue('nfsen_datadir') . '*', GLOB_ONLYDIR);

        $sources = "";
        for ($i = 0; $i < count($dirs); $i ++) {
            $sources .= basename($dirs [$i]);

            if ($i != (count($dirs) - 1)) {
                $sources .= ':';
            }
        }

        return $sources;
    }

}
