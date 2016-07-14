<?php
include_once (ROOT_DIR . '/lib/ConfigManager.php');

function logToSyslog($message, $severity){
    $configmanager = new ConfigManager();
    $syslog = $configmanager->getSettingValue('syslog');
    
    if($syslog == '1'){
        openlog('DDoSScan',LOG_NDELAY, LOG_LOCAL0);
        syslog($severity, $message);
        closelog();
    }
}

