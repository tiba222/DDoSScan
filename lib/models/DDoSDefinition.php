<?php

class DDoSDefinition {

    public $id;
    public $description;
    public $protocol;
    public $src_port;
    public $dst_port;
    public $nfdump_filter;
    public $primary_identifier;

    public function __construct($id, $description, $protocol, $src_port, $dst_port, $nfdump_filter, $primary_identifier) {
        $this->id = $id;
        $this->description = $description;
        $this->protocol = $protocol;
        $this->src_port = $src_port;
        $this->dst_port = $dst_port;
        $this->nfdump_filter = $nfdump_filter;
        $this->primary_identifier = $primary_identifier;
    }

}
