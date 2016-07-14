# Introduction
This tool can scan for DDoS attacks in netflow data and execute actions based on thresholds. Available actions for now are ACL based mitigations by using expect scripts (block or divert traffic)
or sending out alerts using mail. However the tool is written with the idea that creating new actions is easy.

For the ACL based mitigations the tool is currently only supporting IOS-XR, but also here adding support for other router operating systems is quite easy.

# Required software ##
-   NFSen
-   NFDump
-   PHP5
-   MySQL / MariaDB
-   Expect

# Installation
-   Install all required dependencies (see above)
-   Clone the repository in for example the /opt/directory
-   Make sure the worker.php and ddosadmin.php file + all files in the /expect directory have execute permissions
-   Create new database and import the db_install.sql file located in the /database directory
-   (Optional) Import the db_content.sql file located in the /database directory. This is not required but it will give you some predefined definitions and thresholds
-   Change database configuration in config.php
-   Create cron job that executes worker.php every 5 minutes
-   (Optional) Create symlink to ddosadmin.php in for example the /usr/bin directory, this will make it possible to use the CLI interface without specifying the complete path each time

# Configuration
Configuration is for the moment done via the basic CLI interface provided by the ddosadmin.php script. The current application configuration can be viewed with the 'ddosadmin show config' command. 
Settings can be changed with the 'ddosadmin config change-setting <setting> <value>' command.

## Available config options:
````
syslog => 0 or 1, enable or disable syslog logging.
scan_top_n => Define the maximum number of victims to find. This setting is used for limiting NFDump output so a big value here can impact scanning performance.
scan_delay => Number of seconds to wait after executing job, this can be used to make sure the latest netflow data is uploaded by all routers.   
ddos_interval => Maximum time between traffic (in minutes) to consider it part of the same DDoS attack.
nfsen_datadir => Location of the live profile data of your NFSen installation
nfdump_location => Location of the nfdump binary
netflow sampling => Sampling rate of the netflow data configured on your routers, be aware that setting this false can give strange results.
def_autoremove_days => Number of days to make mitigations last when no other value is specified in the action parameters.
````

## Configuring scanning subnets
Also you will have to configure your own subnets (the subnets you want to protect), this is done with the 'ddosadmin config add-subnet <cidr> <description>' 
and the 'ddosadmin config delete-subnet <cidr>' command.

# CLI Help 
```
ddosadmin assign action <action_id> <threshold_id>

ddosadmin config show
ddosadmin config add-subnet <cidr> <description>
ddosadmin config delete-subnet <cidr>
ddosadmin config change-setting <setting> <value>

ddosadmin create acl <router_id> <name> <type: outside or inside> <seq_start> <seq_end>
ddosadmin create action <description> <action> <action parameters: key=value;key=value> <once>
ddosadmin create definition <description> <protocol> <source port> <destination port> <nfdump filter> <primary identifier>
ddosadmin create exclusion <cidr> <excluded action>
ddosadmin create mail-alert <cidr> <email>
ddosadmin create router <name> <type> <mgmt_ip> <username> <password> <enable_password> <protected_vrf> <outside_vrf>
ddosadmin create threshold <ddos_definition_id> <priority> <bps> <pps> <fps> <trend_use> <trend_window> <trend_hits>

ddosadmin delete acl <id>
ddosadmin delete action <id>
ddosadmin delete definition <id>
ddosadmin delete exclusion <id>
ddosadmin delete router <id>
ddosadmin delete mail-alert <id>
ddosadmin delete threshold <id>

ddosadmin list acls [json]
ddosadmin list actions [json]
ddosadmin list active-attacks [json]
ddosadmin list definitions [json]
ddosadmin list exclusions [json]
ddosadmin list mail-alerts
ddosadmin list routers [json]
ddosadmin list thresholds [json]

ddosadmin unassign action <action_id> <threshold_id>

ddosadmin show definition <id>
ddosadmin show router <id>
ddosadmin show threshold <id>
```