<?php

include_once (ROOT_DIR . '/lib/functions/db.php');
include_once (ROOT_DIR . '/lib/models/Router.php');

class RouterManager {

    protected $db;

    public function __construct() {
        $this->db = getDB();
    }

    /**
     *
     */
    public function listRouters() {
        $query = $this->db->query("SELECT * FROM ddos_routers");

        $routers = array();
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $router = new Router($row ['id'], $row ['name'], $row ['type'], $row ['mgmt_ip'], $row ['username'], $row ['password'], $row['enable_password'], $row['protected_vrf'], $row['outside_vrf']);
            $routers [] = $router;
        }

        return $routers;
    }

    /**
     *
     */
    public function getRouterById($id) {
        $query = $this->db->prepare("SELECT * FROM ddos_routers WHERE id = :id");
        $query->bindParam(':id', $id);
        $query->execute();

        $row = $query->fetch(PDO::FETCH_ASSOC);
        $router = new Router($row ['id'], $row ['name'], $row ['type'], $row ['mgmt_ip'], $row ['username'], $row ['password'], $row['enable_password'], $row['protected_vrf'], $row['outside_vrf']);

        return $router;
    }

    /**
     *
     */
    public function createRouter($name, $type, $mgmt_ip, $username, $password, $enable_password, $protected_vrf, $outside_vrf) {
        $query = $this->db->prepare("INSERT INTO ddos_routers (name, type, mgmt_ip, username, password, enable_password, protected_vrf, outside_vrf) VALUES (:name, :type, :mgmt_ip, :username, :password, :enable_password, :protected_vrf, :outside_vrf)");
        $query->bindParam(':name', $name);
        $query->bindParam(':type', $type);
        $query->bindParam(':mgmt_ip', $mgmt_ip);
        $query->bindParam(':username', $username);
        $query->bindParam(':password', $password);
        $query->bindParam(':enable_password', $enable_password);
        $query->bindParam(':protected_vrf', $protected_vrf);
        $query->bindParam(':outside_vrf', $outside_vrf);

        $query->execute();

        return $this->getRouterById($this->db->lastInsertId());
    }
    
    public function deleteRouterById($id) {
        $query = $this->db->prepare("DELETE FROM ddos_routers WHERE id = :id");
        $query->bindParam(':id', $id);

        $query->execute();
    }

    /**
     *
     */
    public function updateRouter($router) {
        $query = $this->db->prepare("UPDATE ddos_routers SET name = :name, type = :type, mgmt_ip = :mgmt_ip, username = :username, password = :password, enable_password = :enable_password, protected_vrf = :protected_vrf, outside_vrf = :outside_vrf WHERE id = :id");

        $query->bindParam(':id', $router->id);
        $query->bindParam(':name', $router->name);
        $query->bindParam(':type', $router->type);
        $query->bindParam(':mgmt_ip', $router->mgmt_ip);
        $query->bindParam(':username', $router->username);
        $query->bindParam(':password', $router->password);
        $query->bindParam(':enable_password', $router->enable_password);
        $query->bindParam(':protected_vrf', $router->protected_vrf);
        $query->bindParam(':outside_vrf', $router->outside_vrf);

        $query->execute();
    }

}

?>