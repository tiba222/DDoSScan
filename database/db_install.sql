SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `ddos_monitoring`
--

-- --------------------------------------------------------

--
-- Table structure for table `ddos_config`
--

CREATE TABLE IF NOT EXISTS `ddos_config` (
  `setting` varchar(32) NOT NULL,
  `value` varchar(512) NOT NULL,
  PRIMARY KEY (`setting`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Table structure for table `ddos_config_subnets`
--

CREATE TABLE IF NOT EXISTS `ddos_config_subnets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subnet` varchar(32) NOT NULL,
  `description` varchar(512) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Table structure for table `ddos_type`
--

CREATE TABLE IF NOT EXISTS `ddos_definition` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(512) NOT NULL,
  `protocol` varchar(32) NOT NULL,
  `src_port` varchar(32) NOT NULL,
  `dst_port` varchar(32) NOT NULL,
  `nfdump_filter` varchar(1024) NOT NULL,
  `primary_identifier` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Table structure for table `ddos_atack`
--

CREATE TABLE IF NOT EXISTS `ddos_attack` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ddos_type_id` int(11) NOT NULL,
  `time_start` DATETIME DEFAULT NULL,
  `time_last_traffic` DATETIME DEFAULT NULL,
  `target_ip` varchar(32) NOT NULL,
  `active` int(5),
  PRIMARY KEY (`id`),
  FOREIGN KEY (ddos_type_id) REFERENCES ddos_definition(id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

--
-- Table structure for table `ddos_atack_entry`
--

CREATE TABLE IF NOT EXISTS `ddos_attack_entry` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ddos_attack_id` int (11) NOT NULL,
  `timestamp` DATETIME DEFAULT NULL,
  `bps` bigint(30) NOT NULL,
  `pps` bigint(30) NOT NULL,
  `fps` bigint(30) NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (ddos_attack_id) REFERENCES ddos_attack(id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

--
-- Table structure for table `ddos_attack_threshold`
--

CREATE TABLE IF NOT EXISTS `ddos_attack_threshold` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ddos_type_id` int(11) NOT NULL,
  `priority` int(11) NOT NULL,
  `bps_threshold` bigint(30) NOT NULL,
  `pps_threshold` bigint(30) NOT NULL,
  `fps_threshold` bigint(30) NOT NULL,
  `trend_use` int(11) NOT NULL,
  `trend_window` int(30) NOT NULL,
  `trend_hits` int(30) NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (ddos_type_id) REFERENCES ddos_definition(id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Table structure for table `ddos_action`
--

CREATE TABLE IF NOT EXISTS `ddos_action` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(1024),
  `action` varchar(32) NOT NULL,
  `action_parameters` varchar(1024) NOT NULL,
  `once` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Table structure for table `ddos_action_history`
--

CREATE TABLE IF NOT EXISTS `ddos_action_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ddos_attack_id` int(11) NOT NULL,
  `action_id` int(11) NOT NULL,
  `executed_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (ddos_attack_id) REFERENCES ddos_attack(id),
  FOREIGN KEY (action_id) REFERENCES ddos_action(id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Table structure for table `ddos_threshold_action`
--

CREATE TABLE IF NOT EXISTS `ddos_threshold_action` (
  `threshold_id` int(11) NOT NULL,
  `action_id` int(11) NOT NULL,
  PRIMARY KEY (threshold_id, action_id),
  FOREIGN KEY (threshold_id) REFERENCES ddos_attack_threshold(id),
  FOREIGN KEY (action_id) REFERENCES ddos_action(id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- Table structure for table `ddos_mitigation_action`
--

CREATE TABLE IF NOT EXISTS `ddos_mitigation_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ddos_attack_id` int(11) NOT NULL,
  `mitigated_at` DATETIME DEFAULT NULL,
  `last_traffic` DATETIME DEFAULT NULL,
  `autoremove` BOOLEAN NOT NULL,
  `autoremove_days` int(11),
  `reason` varchar(512) NOT NULL,
  `active` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (ddos_attack_id) REFERENCES ddos_attack(id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Table structure for table `ddos_mitigation_routers`
--

CREATE TABLE IF NOT EXISTS `ddos_routers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `type` varchar(64) NOT NULL,
  `mgmt_ip` varchar(32) NOT NULL,
  `username` varchar(64) NOT NULL,
  `password` varchar(128) NOT NULL,
  `enable_password` varchar(128) NOT NULL,
  `protected_vrf` varchar(64),
  `outside_vrf` varchar(64),
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

--
-- Table structure for table `ddos_acl`
--

CREATE TABLE IF NOT EXISTS `ddos_acl` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `router_id` int(11) NOT NULL,
  `type` varchar(32) NOT NULL,
  `name` varchar(256) NOT NULL,
  `seq_start` int(6) NOT NULL,
  `seq_end` int(6) NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (router_id) REFERENCES ddos_mitigation_routers(id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
	
--
-- Table structure for table `ddos_acl_entry`
--

CREATE TABLE IF NOT EXISTS `ddos_acl_entry` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `acl_id` int(11) NOT NULL,
  `ddos_attack_id` int(11) NOT NULL,
  `seq` int(6) NOT NULL,
  `content` varchar(512) NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (acl_id) REFERENCES ddos_mitigation_routers_acl(id),
  FOREIGN KEY (ddos_attack_id) REFERENCES ddos_attack(id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

--
-- Table structure for table `ddos_mail_alerts`
--

CREATE TABLE IF NOT EXISTS `ddos_mail_alerts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `target` varchar(32) NOT NULL,
  `email` varchar(256) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

--
-- Table structure for table `ddos_ip_exclusions`
--

CREATE TABLE IF NOT EXISTS `ddos_ip_exclusions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `target` varchar(32) NOT NULL,
  `excluded_action` varchar(32) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `ddos_ip_exclusions`
--
INSERT INTO `ddos_config` VALUES
('syslog', '1'),
('scan_top_n', '20'),
('scan_delay', '20'),
('ddos_interval', '60'),
('nfsen_datadir', '/data/nfsen/profiles-data/live/'),
('nfdump_location', '/usr/local/bin/nfdump'),
('netflow_sampling', '100'),
('def_autoremove_days', 10);

-- 
-- Dumping data for table `ddos_action`
--
INSERT INTO `ddos_action` (id, description, action, action_parameters, once) VALUES
(1, 'Send E-Mail alerts', 'alert_email', '', 1),
(2, 'Mitigate ACL Default', 'mitigate_acl', '', 0);