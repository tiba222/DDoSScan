<?php

class ACLEntry {

    public $id;
    public $acl_id;
    public $ddos_attack_id;
    public $seq;
    public $content;

    public function __construct($id, $acl_id, $ddos_attack_id, $seq, $content) {
        $this->id = $id;
        $this->acl_id = $acl_id;
        $this->ddos_attack_id = $ddos_attack_id;
        $this->seq = $seq;
        $this->content = $content;
    }

}
