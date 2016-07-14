<?php

class DDoSAttackEntry {

    public $id;
    public $ddos_attack_id;
    public $timestamp;
    public $bps;
    public $pps;
    public $fps;

    public function __construct($id, $ddos_attack_id, $timestamp, $bps, $pps, $fps) {
        $this->id = $id;
        $this->ddos_attack_id = $ddos_attack_id;
        $this->timestamp = $timestamp;
        $this->bps = $bps;
        $this->pps = $pps;
        $this->fps = $fps;
    }

}
