--
-- Dumping data for table `ddos_type`
--
INSERT INTO `ddos_definition` VALUES 
(1,'DNS Amplification','udp','53','any','proto udp && (src port 53 || src port 0)','flows'),
(2,'NTP Amplification','udp','123','any','proto udp && src port 123','pps'),
(3,'Chargen','udp','19','any','proto udp && src port 19','pps'),
(4,'SNMP Amplification','udp','161','any','proto udp && src port 161','flows'),
(5,'ICMP Flooding','icmp','any','any','proto icmp','pps'),
(6,'QotD Amplification','udp','17','any','proto udp && src port 17','flows'),
(7,'SSDP Amplification','udp','1900','any','proto udp && src port 1900','flows'),
(8,'RIPv1 Amplification','udp','520','any','proto udp && src port 520','pps'),
(9,'Portmapper Amplification','udp','111','any','proto udp && src port 111','flows'),
(10,'UDP/80','udp','any','80','proto udp && dst port 80','flows'),
(11,'Steam Port 27015 amplification','udp','27015','27015','proto udp && src port 27015 && dst port 27015','flows');

--
-- Dumping data for table `ddos_attack_threshold`
--
INSERT INTO `ddos_attack_threshold` (id, ddos_type_id, priority, bps_threshold, pps_threshold, fps_threshold, trend_use, trend_window, trend_hits) VALUES
(1, 1, 10, -1, -1, 1000000, 1, 24, 2),
(2, 1, 20, -1, -1, 2000000, 0, 0, 0),
(3, 2, 10, -1, 250000, -1, 1, 24, 2),
(4, 2, 20, -1, 400100, -1, 0, 0, 0),
(5, 3, 10, -1, 10000, -1, 1, 24, 2),
(6, 4, 10, -1, -1, 1000000, 1, 24, 2),
(7, 4, 20, -1, -1, 2000000, 0, 0, 0),
(8, 5, 10, -1, 500000, -1, 1, 24, 2),
(9, 5, 20, -1, 1000000, -1, 0, 0, 0),
(10, 6, 10, -1, -1, 1000000, 1, 24, 2),
(11, 6, 20, -1, -1, 2000000, 0, 0, 0),
(12, 7, 10, -1, -1, 1000000, 1, 24, 2),
(13, 7, 20, -1, -1, 2000000, 0, 0, 0),
(14, 8, 10, -1, 200000, -1, 1, 24, 2),
(15, 8, 20, -1, 500000, -1, 0, 0, 0),
(16, 9, 10, -1, -1, 1000000, 1, 24, 2),
(17, 9, 20, -1, -1, 2000000, 0, 0, 0),
(18, 10, 10, -1, -1, 1000000, 1, 24, 2),
(19, 10, 20, -1, -1, 2000000, 0, 0, 0),
(20, 11, 10, -1, -1, 1000000, 1, 24, 2),
(21, 11, 20, -1, -1, 2000000, 0, 0, 0);

--
-- Dumping data for table `ddos_threshold_action`
--
INSERT INTO `ddos_threshold_action` (threshold_id, action_id) VALUES
(1,1),
(2,1),
(3,1),
(4,1),
(5,1),
(6,1),
(7,1),
(8,1),
(9,1),
(10,1),
(11,1),
(12,1),
(13,1),
(14,1),
(15,1),
(16,1),
(17,1),
(18,1),
(19,1),
(20,1),
(21,1),
(1,2),
(2,2),
(3,2),
(4,2),
(5,2),
(6,2),
(7,2),
(8,2),
(9,2),
(10,2),
(11,2),
(12,2),
(13,2),
(14,2),
(15,2),
(16,2),
(17,2),
(18,2),
(19,2),
(20,2),
(21,2);