<?php

class DDoSThreshold {

    public $id;
    public $ddos_type_id;
    public $priority;
    public $bps_threshold;
    public $pps_threshold;
    public $fps_threshold;
    public $trend_use;
    public $trend_window;
    public $trend_hits;

    public function __construct($id, $ddos_type_id, $priority, $bps_threshold, $pps_threshold, $fps_threshold, $trend_use, $trend_window, $trend_hits) {
        $this->id = $id;
        $this->ddos_type_id = $ddos_type_id;
        $this->priority = $priority;
        $this->bps_threshold = $bps_threshold;
        $this->pps_threshold = $pps_threshold;
        $this->fps_threshold = $fps_threshold;
        $this->trend_use = $trend_use;
        $this->trend_window = $trend_window;
        $this->trend_hits = $trend_hits;
    }

}
