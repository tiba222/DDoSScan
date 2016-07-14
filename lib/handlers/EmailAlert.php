<?php

include_once (ROOT_DIR . '/lib/handlers/ActionHandler.php');
include_once (ROOT_DIR . '/lib/MailAlertManager.php');
include_once (ROOT_DIR . '/lib/models/DDoSDefinition.php');
include_once (ROOT_DIR . '/lib/functions/log.php');

class EmailAlert implements ActionHandler {

    protected $manager;

    public function __construct() {
        $this->manager = new MailAlertManager();
    }
    
    public function preScan() {
        return;
    }

    public function onDDoS($action, $data, $definition, $attack) {      
        $bw_mbps = $data[12] / 1000000;

        $message = "This is an automatically generated message by the DDoSScan system running at " . gethostname() . "\n" .
                "\n" .
                "The system detected a DDoS attack against $data[4]:\n" .
                "\n" .
                "DDoS Type:\t\t $definition->description \n" .
                "Bandwidth:\t\t $bw_mbps Mb/s \n" .
                "Packetrate:\t\t $data[11] Packets/s \n" .
                "Flowrate:\t\t $data[5] Flows/s \n";

        foreach ($this->manager->listMailAlertsByTarget($data[4]) as $alert) {
            mail($alert->email, 'DDoS Notification', $message);
            logToSyslog("EmailAlert: Send notification mail to $alert->email", LOG_INFO);
        }
        
        return true;
    }
    
    public function postScan() {
        return;
    }

}
