<?php

class ACL {

    public $id;
    public $router_id;
    public $name;
    public $type;
    public $seq_start;
    public $seq_end;

    public function __construct($id, $router_id, $name, $type, $seq_start, $seq_end) {
        $this->id = $id;
        $this->router_id = $router_id;
        $this->name = $name;
        $this->type = $type;
        $this->seq_start = $seq_start;
        $this->seq_end = $seq_end;
    }

}
