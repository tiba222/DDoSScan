<?php

class Router {

    public $id;
    public $name;
    public $type;
    public $mgmt_ip;
    public $username;
    public $password;
    public $enable_password;
    public $protected_vrf;
    public $outside_vrf;

    public function __construct($id, $name, $type, $mgmt_ip, $username, $password, $enable_password, $protected_vrf, $outside_vrf) {
        $this->id = $id;
        $this->name = $name;
        $this->type = $type;
        $this->mgmt_ip = $mgmt_ip;
        $this->username = $username;
        $this->password = $password;
        $this->enable_password = $enable_password;
        $this->protected_vrf = $protected_vrf;
        $this->outside_vrf = $outside_vrf;
    }

}
