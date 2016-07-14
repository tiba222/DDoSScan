<?php

class DDoSAttack {

    public $id;
    public $ddos_type_id;
    public $time_start;
    public $time_last_traffic;
    public $target_ip;
    public $active;

    public function __construct($id, $ddos_type_id, $time_start, $time_last_traffic, $target_ip, $active) {
        $this->id = $id;
        $this->ddos_type_id = $ddos_type_id;
        $this->time_start = $time_start;
        $this->time_last_traffic = $time_last_traffic;
        $this->target_ip = $target_ip;
        $this->active = $active;
    }

}
