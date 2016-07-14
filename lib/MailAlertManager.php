<?php

include_once (ROOT_DIR . '/lib/functions/db.php');
include_once (ROOT_DIR . '/lib/functions/ipv4.php');
include_once (ROOT_DIR . '/lib/models/MailAlert.php');

class MailAlertManager {

    protected $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function createMailAlert($target, $email) {
        $query = $this->db->prepare("INSERT INTO ddos_mail_alerts (target, email) VALUES (:target, :email)");
        $query->bindParam(':target', $target);
        $query->bindParam(':email', $email);

        $query->execute();

        return $this->getMailAlertById($this->db->lastInsertId());
    }

    public function updateMailAlert($mailalert) {
        $query = $this->db->prepare("UPDATE ddos_mail_alerts SET target = :target, email = :email WHERE id = :id");

        $query->bindParam(':id', $mailalert->id);
        $query->bindParam(':target', $mailalert->target);
        $query->bindParam(':email', $mailalert->email);

        $query->execute();
    }

    public function deleteMailAlertById($id) {
        $query = $this->db->prepare("DELETE FROM ddos_mail_alerts WHERE id = :id");

        $query->bindParam(':id', $id);
        $query->execute();
        
        return true;
    }

    public function listMailAlertsByTarget($target) {
        $query = $this->db->prepare("SELECT * FROM ddos_mail_alerts");
        $query->execute();

        $alerts = array();
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            if (belongsToSubnet($target, $row['target'])) {
                $alert = new MailAlert($row ['id'], $row['target'], $row ['email']);
                $alerts [] = $alert;
            }
        }

        return $alerts;
    }

    public function listMailAlerts() {
        $query = $this->db->prepare("SELECT * FROM ddos_mail_alerts");
        $query->execute();

        $alerts = array();
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {

            $alert = new MailAlert($row ['id'], $row['target'], $row ['email']);
            $alerts [] = $alert;
        }

        return $alerts;
    }

    public function getMailAlertById($id) {
        $query = $this->db->prepare("SELECT * FROM ddos_mail_alerts WHERE id = :id");
        $query->bindParam(':id', $id);
        $query->execute();

        $row = $query->fetch(PDO::FETCH_ASSOC);
        $alert = new MailAlert($row ['id'], $row['target'], $row ['email']);

        return $alert;
    }

}
