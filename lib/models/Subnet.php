<?php

class Subnet {

    public $id;
    public $subnet;
    public $description;

    public function __construct($id, $subnet, $description) {
        $this->id = $id;
        $this->subnet = $subnet;
        $this->description = $description;
    }

}
