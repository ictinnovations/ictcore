SET NAMES 'utf8';
SET CHARACTER SET utf8;

/*******************************************************************/
/* Copyright © 2014 ICT Innovations Pakistan All Rights Reserved   */
/* Developed By: Nasir Iqbal                                       */
/* Website : http://www.ictinnovations.com/                        */
/* Mail : nasir@ictinnovations.com                                 */
/*******************************************************************/

/*==============================================================*/
/* Table: account                                               */
/*==============================================================*/
CREATE TABLE account
(
   account_id                     int(11) unsigned       NOT NULL auto_increment,
   type                           varchar(32)            default NULL,
   username                       varchar(64)            default NULL,
   passwd                         varchar(128)           default NULL,
   passwd_pin                     varchar(32)            default NULL,
   first_name                     varchar(128)           default NULL,
   last_name                      varchar(128)           default NULL,
   phone                          varchar(16)            default NULL,
   email                          varchar(128)           default NULL,
   address                        varchar(128)           default NULL,
   active                         int(1)                 NOT NULL default 0,
   settings                       text,
   date_created                   int(11)                default NULL,
   created_by                     int(11)                default NULL,
   last_updated                   int(11)                default NULL,
   updated_by                     int(11) unsigned       default NULL,
   PRIMARY KEY  (account_id),
   UNIQUE KEY username (type, username)
) ENGINE = InnoDB;

/*==============================================================*/
/* Table: usr                                                   */
/*==============================================================*/
CREATE TABLE usr
(
   usr_id                         int(11) unsigned       NOT NULL auto_increment,
   role_id                        int(11) unsigned       NOT NULL default '0',
   username                       varchar(64)            default NULL,
   passwd                         varchar(128)           default NULL,
   first_name                     varchar(128)           default NULL,
   last_name                      varchar(128)           default NULL,
   phone                          varchar(16)            default NULL,
   mobile                         varchar(16)            default NULL,
   email                          varchar(128)           default NULL,
   address                        varchar(128)           default NULL,
   company                        varchar(128)           default NULL,
   country_id                     int(11)                default NULL,
   language_id                    varchar(2)             default NULL,
   timezone_id                    int(11)                default NULL,
   active                         int(1)                 NOT NULL default 0,
   date_created                   int(11)                default NULL,
   created_by                     int(11)                default NULL,
   last_updated                   int(11)                default NULL,
   updated_by                     int(11) unsigned       default NULL,
   PRIMARY KEY  (usr_id),
   UNIQUE KEY username (username),
   UNIQUE KEY email (email)
) ENGINE = InnoDB;

DELIMITER |
CREATE TRIGGER usr_insert AFTER INSERT
  ON usr FOR EACH ROW BEGIN
    INSERT INTO account (account_id, type, username, passwd, passwd_pin, first_name, last_name, phone, email, address,
                         active, date_created, created_by, last_updated, updated_by)
    SELECT NULL, 'account', NEW.username, NEW.passwd, LEFT(RAND()*999999, 4), NEW.first_name, NEW.last_name, NEW.phone,
           NEW.email, NEW.address, NEW.active, NEW.date_created, NEW.usr_id, NULL, NULL;
  END;
|
CREATE TRIGGER usr_update AFTER UPDATE
  ON usr FOR EACH ROW BEGIN
    UPDATE account SET email = NEW.email , phone = NEW.phone WHERE type = 'account' AND created_by = NEW.usr_id limit 1;
  END; 
|
DELIMITER ;

/*==============================================================*/
/* Table: role                                                  */
/*==============================================================*/
CREATE TABLE role
(
   role_id                        int(11) unsigned       NOT NULL auto_increment,
   name                           varchar(255)           default NULL,
   description                    text,
   date_created                   int(11)                default NULL,
   created_by                     int(11)                default NULL,
   last_updated                   int(11)                default NULL,
   updated_by                     int(11) unsigned       default NULL,
   PRIMARY KEY  (role_id)
) ENGINE = InnoDB;

/*==============================================================*/
/* Table: user_role                                              */
/*==============================================================*/
CREATE TABLE user_role
(
   role_id                        int(11) unsigned       default NULL,
   usr_id                         int(11) unsigned       default NULL
) ENGINE = InnoDB;

/*==============================================================*/
/* Table: permission                                            */
/*==============================================================*/
CREATE TABLE permission
(
   permission_id                  int(11) unsigned       NOT NULL auto_increment,
   name                           varchar(255)           default NULL,
   description                    text,
   PRIMARY KEY  (permission_id),
   UNIQUE KEY name (name)
) ENGINE = InnoDB;

/*==============================================================*/
/* Table: user_permission                                       */
/*==============================================================*/
CREATE TABLE user_permission
(
   user_permission_id             int(11) unsigned       NOT NULL auto_increment,
   usr_id                         int(11) unsigned       NOT NULL default '0',
   permission_id                  int(11) unsigned       NOT NULL default '0',
   PRIMARY KEY  (user_permission_id),
   KEY usr_id (usr_id),
   KEY permission_id (permission_id)
) ENGINE = InnoDB;

/*==============================================================*/
/* Table: role_permission                                       */
/*==============================================================*/
CREATE TABLE role_permission 
(
   role_permission_id             int(11) unsigned       NOT NULL auto_increment,
   role_id                        int(11) unsigned       NOT NULL default '0',
   permission_id                  int(11) unsigned       NOT NULL default '0',
   PRIMARY KEY  (role_permission_id),
   KEY permission_id (permission_id),
   KEY role_id (role_id)
) ENGINE = InnoDB;

/*==============================================================*/
/* Table: insert core permissions                               */
/*==============================================================*/
-- Common permissions
INSERT INTO permission VALUES (NULL, 'api', '');
INSERT INTO permission VALUES (NULL, 'api_access', '');
INSERT INTO permission VALUES (NULL, 'statistic', '');
INSERT INTO permission VALUES (NULL, 'statistic_read', '');
INSERT INTO permission VALUES (NULL, 'statistic_admin', '');
INSERT INTO permission VALUES (NULL, 'configuration', '');
INSERT INTO permission VALUES (NULL, 'configuration_read', '');
INSERT INTO permission VALUES (NULL, 'configuration_write', '');
-- Program permissions
INSERT INTO permission VALUES (NULL, 'program', '');
INSERT INTO permission VALUES (NULL, 'program_create', '');
INSERT INTO permission VALUES (NULL, 'program_list', '');
INSERT INTO permission VALUES (NULL, 'program_read', '');
INSERT INTO permission VALUES (NULL, 'program_delete', '');
INSERT INTO permission VALUES (NULL, 'program_execute', '');
INSERT INTO permission VALUES (NULL, 'program_admin', '');
-- Transmission permissions
INSERT INTO permission VALUES (NULL, 'transmission', '');
INSERT INTO permission VALUES (NULL, 'transmission_create', '');
INSERT INTO permission VALUES (NULL, 'transmission_list', '');
INSERT INTO permission VALUES (NULL, 'transmission_read', '');
INSERT INTO permission VALUES (NULL, 'transmission_update', '');
INSERT INTO permission VALUES (NULL, 'transmission_delete', '');
INSERT INTO permission VALUES (NULL, 'transmission_send', '');
INSERT INTO permission VALUES (NULL, 'transmission_admin', '');
-- Task permissions
INSERT INTO permission VALUES (NULL, 'task', '');
INSERT INTO permission VALUES (NULL, 'task_create', '');
INSERT INTO permission VALUES (NULL, 'task_read', '');
INSERT INTO permission VALUES (NULL, 'task_list', '');
INSERT INTO permission VALUES (NULL, 'task_delete', '');
INSERT INTO permission VALUES (NULL, 'task_admin', '');
-- Schedule permissions
INSERT INTO permission VALUES (NULL, 'schedule', '');
INSERT INTO permission VALUES (NULL, 'schedule_create', '');
INSERT INTO permission VALUES (NULL, 'schedule_read', '');
INSERT INTO permission VALUES (NULL, 'schedule_list', '');
INSERT INTO permission VALUES (NULL, 'schedule_delete', '');
INSERT INTO permission VALUES (NULL, 'schedule_admin', '');
-- Spool permissions
INSERT INTO permission VALUES (NULL, 'spool', '');
INSERT INTO permission VALUES (NULL, 'spool_read', '');
INSERT INTO permission VALUES (NULL, 'spool_list', '');
INSERT INTO permission VALUES (NULL, 'spool_admin', '');
-- Result permissions
INSERT INTO permission VALUES (NULL, 'result', '');
INSERT INTO permission VALUES (NULL, 'result_read', '');
INSERT INTO permission VALUES (NULL, 'result_list', '');
INSERT INTO permission VALUES (NULL, 'result_admin', '');
-- Provider permissions
INSERT INTO permission VALUES (NULL, 'provider', '');
INSERT INTO permission VALUES (NULL, 'provider_create', '');
INSERT INTO permission VALUES (NULL, 'provider_list', '');
INSERT INTO permission VALUES (NULL, 'provider_read', '');
INSERT INTO permission VALUES (NULL, 'provider_update', '');
INSERT INTO permission VALUES (NULL, 'provider_delete', '');
INSERT INTO permission VALUES (NULL, 'provider_admin', '');
-- Contact permissions
INSERT INTO permission VALUES (NULL, 'contact', '');
INSERT INTO permission VALUES (NULL, 'contact_create', '');
INSERT INTO permission VALUES (NULL, 'contact_list', '');
INSERT INTO permission VALUES (NULL, 'contact_read', '');
INSERT INTO permission VALUES (NULL, 'contact_update', '');
INSERT INTO permission VALUES (NULL, 'contact_delete', '');
INSERT INTO permission VALUES (NULL, 'contact_admin', '');
-- GROUP permissions
INSERT INTO permission VALUES (NULL, 'group', '');
INSERT INTO permission VALUES (NULL, 'group_create', '');
INSERT INTO permission VALUES (NULL, 'group_list', '');
INSERT INTO permission VALUES (NULL, 'group_read', '');
INSERT INTO permission VALUES (NULL, 'group_update', '');
INSERT INTO permission VALUES (NULL, 'group_delete', '');
INSERT INTO permission VALUES (NULL, 'group_admin', '');
-- Account permissions
INSERT INTO permission VALUES (NULL, 'account', '');
INSERT INTO permission VALUES (NULL, 'account_create', '');
INSERT INTO permission VALUES (NULL, 'account_list', '');
INSERT INTO permission VALUES (NULL, 'account_read', '');
INSERT INTO permission VALUES (NULL, 'account_update', '');
INSERT INTO permission VALUES (NULL, 'account_delete', '');
INSERT INTO permission VALUES (NULL, 'account_admin', '');
-- User permissions
INSERT INTO permission VALUES (NULL, 'user', '');
INSERT INTO permission VALUES (NULL, 'user_create', '');
INSERT INTO permission VALUES (NULL, 'user_list', '');
INSERT INTO permission VALUES (NULL, 'user_read', '');
INSERT INTO permission VALUES (NULL, 'user_password', '');
INSERT INTO permission VALUES (NULL, 'user_update', '');
INSERT INTO permission VALUES (NULL, 'user_delete', '');
INSERT INTO permission VALUES (NULL, 'user_admin', '');
INSERT INTO permission VALUES (NULL, 'usr', '');
INSERT INTO permission VALUES (NULL, 'usr_create', '');
INSERT INTO permission VALUES (NULL, 'usr_list', '');
INSERT INTO permission VALUES (NULL, 'usr_read', '');
INSERT INTO permission VALUES (NULL, 'usr_password', '');
INSERT INTO permission VALUES (NULL, 'usr_update', '');
INSERT INTO permission VALUES (NULL, 'usr_delete', '');
INSERT INTO permission VALUES (NULL, 'usr_admin', '');
-- Role permissions
INSERT INTO permission VALUES (NULL, 'role', '');
INSERT INTO permission VALUES (NULL, 'role_create', '');
INSERT INTO permission VALUES (NULL, 'role_list', '');
INSERT INTO permission VALUES (NULL, 'role_read', '');
INSERT INTO permission VALUES (NULL, 'role_update', '');
INSERT INTO permission VALUES (NULL, 'role_delete', '');
-- Permission related permissions
INSERT INTO permission VALUES (NULL, 'permission', '');
INSERT INTO permission VALUES (NULL, 'permission_create', '');
INSERT INTO permission VALUES (NULL, 'permission_read', '');
INSERT INTO permission VALUES (NULL, 'permission_list', '');
INSERT INTO permission VALUES (NULL, 'permission_delete', '');
-- Campaign permissions
INSERT INTO permission VALUES (NULL, 'campaign', '');
INSERT INTO permission VALUES (NULL, 'campaign_create', '');
INSERT INTO permission VALUES (NULL, 'campaign_list', '');
INSERT INTO permission VALUES (NULL, 'campaign_read', '');
INSERT INTO permission VALUES (NULL, 'campaign_update', '');
INSERT INTO permission VALUES (NULL, 'campaign_delete', '');
INSERT INTO permission VALUES (NULL, 'campaign_start', '');
INSERT INTO permission VALUES (NULL, 'campaign_stop', '');
INSERT INTO permission VALUES (NULL, 'campaign_admin', '');

/*==============================================================*/
/* Table: resource                                              */
/*==============================================================*/
CREATE TABLE resource
(
   resource_id                    int(11) unsigned       NOT NULL auto_increment,
   name                           varchar(64)            NOT NULL,
   description                    varchar(255)           NOT NULL default '',
   data                           varchar(255)           NOT NULL default '',
   PRIMARY KEY  (resource_id)
) ENGINE = InnoDB;
CREATE INDEX resource_name ON resource (name);

/*==============================================================*/
/* Table: user_resource                                         */
/*==============================================================*/
CREATE TABLE user_resource
(
   user_resource_id               int(11) unsigned       NOT NULL auto_increment,
   resource_id                    int(11) unsigned       NOT NULL default '0',
   usr_id                         int(11) unsigned       NOT NULL default '0',
   data                           varchar(255)           NOT NULL default '',
   PRIMARY KEY  (user_resource_id),
   KEY usr_id (usr_id),
   KEY resource_id (resource_id)
) ENGINE = InnoDB;

/*==============================================================*/
/* Table: role_resource                                         */
/*==============================================================*/
CREATE TABLE role_resource
(
   role_resource_id               int(11) unsigned       NOT NULL auto_increment,
   resource_id                    int(11) unsigned       NOT NULL default '0',
   role_id                        int(11) unsigned       NOT NULL default '0',
   data                           varchar(255)           NOT NULL default '',
   PRIMARY KEY  (role_resource_id),
   KEY resource_id (resource_id),
   KEY role_id (role_id)
) ENGINE = InnoDB;

/*==============================================================*/
/* Table: tasks                                                 */
/* Desc: list of todo tasks / schedules                         */
/*==============================================================*/
CREATE TABLE task
(
   task_id                       int(11) unsigned       NOT NULL auto_increment,
   type                          varchar(64)            NOT NULL default '',
   action                        varchar(64)            NOT NULL default 0,
   data                          text,
   weight                        int(4)                 NOT NULL default 0,
   status                        int(4)                 NOT NULL default 0,
   is_recurring                  int(4)                 NOT NULL default 0,
   due_at                        int(11)                default NULL,
   expiry                        int(11)                default NULL,
   last_run                      int(11)                default NULL,
   account_id                    int(11)                default NULL,
   PRIMARY KEY (task_id)
) ENGINE = InnoDB;

CREATE TABLE schedule
(
   year                          varchar(50)            NOT NULL default '*',
   weekday                       varchar(50)            NOT NULL default '*',
   month                         varchar(50)            NOT NULL default '*',
   day                           varchar(50)            NOT NULL default '*',
   hour                          varchar(50)            NOT NULL default '*',
   minute                        varchar(50)            NOT NULL default '*',
   task_id                       int(11) unsigned       NOT NULL
) ENGINE = InnoDB;
CREATE INDEX schedule_task_id ON schedule (task_id);
CREATE INDEX schedule_time ON schedule (hour, minute);
ALTER TABLE schedule 
  ADD FOREIGN KEY schedule_task_delete (task_id) 
  REFERENCES task (task_id) 
    ON DELETE CASCADE 
    ON UPDATE CASCADE;

DELIMITER |
CREATE EVENT event_check_schedule
  ON SCHEDULE EVERY 1 SECOND
  DO BEGIN
    UPDATE task t JOIN schedule s ON t.task_id = s.task_id
    SET t.status = 1, 
        t.due_at = UNIX_TIMESTAMP(),
        t.expiry = (UNIX_TIMESTAMP() + 300)
    WHERE t.status IN (0, 2, 3)
      AND (t.last_run IS NULL OR (t.last_run + 59) < UNIX_TIMESTAMP())
      AND (s.year = '*'     OR s.year = YEAR(CURDATE())) 
      AND (s.month = '*'    OR s.month = MONTH(CURDATE())) 
      AND (s.day = '*'      OR s.day = DAYOFMONTH(CURDATE())) 
      AND (s.weekday = '*'  OR s.weekday = DAYOFWEEK(CURDATE())) 
      AND (s.hour = '*'     OR s.hour = HOUR(CURTIME())) 
      AND (s.minute = '*'   OR s.minute = MINUTE(CURTIME()));
  END;
|
DELIMITER ;

/*==============================================================*/
/* Table: configuration (System and User configurations)        */
/*==============================================================*/
CREATE TABLE configuration
(
   configuration_id               int(11) unsigned       NOT NULL auto_increment,
   type                           varchar(32)            default NULL,
   name                           varchar(32)            default NULL,
   data                           varchar(255)           default NULL,
   permission_flag                int(11) unsigned       default 186,
   PRIMARY KEY (configuration_id)
) ENGINE = InnoDB;

/*==============================================================*/
/* Desc: Dumping Default System configurations                  */
/*==============================================================*/
-- website
-- for ICTCore it is better to have less configurations in database

/*==============================================================*/
/* Table: configuration_data (will store configuration data)    */
/*==============================================================*/
CREATE TABLE configuration_data
(
   configuration_id               int(11)                default NULL,
   class                          int(11)                default 1,
   node_id                        int(11) unsigned       default 0,
   campaign_id                    int(11) unsigned       default NULL,
   data                           varchar(255)           default NULL,
   date_created                   int(11)                default NULL,
   created_by                     int(11)                default NULL,
   last_updated                   int(11)                default NULL,
   updated_by                     int(11) unsigned       default NULL,
   PRIMARY KEY (configuration_id, class, node_id, created_by, campaign_id)
) ENGINE = InnoDB;

/*==============================================================*/
/* Desc: Dumping Default System configurations                  */
/*==============================================================*/
INSERT INTO configuration_data 
(configuration_id, class, node_id, campaign_id, data, date_created, created_by, last_updated, updated_by)
SELECT configuration_id, 1 AS class, 0 AS node_id, NULL, data, UNIX_TIMESTAMP(), 0, NULL, NULL
FROM configuration;

/*==============================================================*/
/* Table: gateway                                               */
/* Desc: list of supported gateways by system                   */
/*==============================================================*/
CREATE TABLE gateway (
   gateway_flag                int(11) unsigned         NOT NULL,
   name                        varchar(64)              NOT NULL default '',
   service_flag                int(11) unsigned         default NULL,
   active                      int(11)                  NOT NULL default 1,
   PRIMARY KEY (gateway_flag)
) ENGINE = InnoDB;

/*==============================================================*/
/* Desc: Dumping sample gateway data into gateway table         */
/*==============================================================*/
-- gateway_flag, name, service_flag, active
-- <?php t('Asterisk'); ?>
INSERT INTO gateway VALUES (1, 'Asterisk', 3, 0); -- voice and fax
-- <?php t('Kannel'); ?>
INSERT INTO gateway VALUES (2, 'Kannel', 4, 1); -- sms
-- <?php t('SwiftMailer'); ?>
INSERT INTO gateway VALUES (4, 'Sendmail', 8, 1); -- email
-- <?php t('Freeswitch'); ?>
INSERT INTO gateway VALUES (8, 'Freeswitch', 3, 1); -- voice and fax

/*==============================================================*/
/* Table: config                                                */
/* Desc: store info about gateways configuration files here     */
/*==============================================================*/
CREATE TABLE config (
   config_id                   int(11) unsigned         NOT NULL auto_increment,
   file_name                   varchar(64)              NOT NULL default '',
   file_path                   varchar(255)             NOT NULL default '',
   source                      varchar(255)             default NULL,
   version                     int(11)                  default 0,
   gateway_flag                int(11) unsigned         default NULL,
   date_created                int(11)                  default NULL,
   created_by                  int(11) unsigned         default NULL,
   last_updated                int(11)                  default NULL,
   updated_by                  int(11) unsigned         default NULL,
   PRIMARY KEY (config_id)
) ENGINE = InnoDB;

/*==============================================================*/
/* Desc: Dumping gateway conf file list into config table       */
/*==============================================================*/
-- core
INSERT INTO config VALUES (1,  'ictcore.conf',              '/usr/ictcore/etc',          NULL, 0, 0, NULL, NULL, NULL, NULL);
INSERT INTO config VALUES (4,  'node.cron',                 '/usr/ictcore/etc',          NULL, 0, 0, NULL, NULL, NULL, NULL);
INSERT INTO config VALUES (6,  'ictcore.ini',               '/usr/ictcore/etc/php',      NULL, 0, 0, NULL, NULL, NULL, NULL);

/*==============================================================*/
/* Table: config_data                                           */
/* Desc: store all gateways configuration data here             */
/*==============================================================*/
CREATE TABLE config_data (
   config_data_id              int(11) unsigned         NOT NULL auto_increment,
   data                        text,
   description                 varchar(128)             NOT NULL default '',
   group_name                  varchar(64)              NOT NULL default '',
   group_child                 varchar(64)              NOT NULL default '',
   file_name                   varchar(64)              NOT NULL default '',
   node_id                     int(11) unsigned         default 0,
   gateway_flag                int(11) unsigned         default NULL,
   date_created                int(11)                  default NULL,
   created_by                  int(11) unsigned         default NULL,
   last_updated                int(11)                  default NULL,
   updated_by                  int(11) unsigned         default NULL,
   PRIMARY KEY (config_data_id)
) ENGINE = InnoDB;

/*==============================================================*/
/* Table: config_node                                           */
/* Desc: define and keep status of configuration file on nodes  */
/*==============================================================*/
CREATE TABLE config_node (
   config_id                   int(11) unsigned         NOT NULL,
   node_id                     int(11) unsigned         NOT NULL,
   version                     int(11)                  NOT NULL default 0,
   date_created                int(11)                  default NULL,
   last_updated                int(11)                  default NULL
);

/*==============================================================*/
/* Table: node                                                  */
/* Desc: list of all nodes which are currently available to     */
/*       this system.                                           */
/*==============================================================*/
CREATE TABLE node (
   node_id                     int(11) unsigned         NOT NULL auto_increment,
   name                        varchar(64)              NOT NULL default '',
   api_host                    varchar(32)              NOT NULL default '',
   api_port                    varchar(16)              NOT NULL default '',
   api_user                    varchar(32)              NOT NULL default '',
   api_pass                    varchar(32)              NOT NULL default '',
   ssh_host                    varchar(32)              NOT NULL default '',
   ssh_port                    varchar(16)              NOT NULL default '',
   ssh_user                    varchar(32)              NOT NULL default '',
   ssh_pass                    varchar(32)              NOT NULL default '',
   gateway_flag                varchar(16)              default NULL,
   channel                     int(11)                  NOT NULL default 0,
   cps                         int(11)                  NOT NULL default 1,
   server_flag                 int(11) unsigned         default NULL,
   active                      int(11)                  NOT NULL default 1,
   weight                      int(11)                  NOT NULL default 0,
   PRIMARY KEY (node_id)
) ENGINE = InnoDB;

/*==============================================================*/
/* Desc: Dumping sample main server data into node table        */
/*==============================================================*/
-- node_id, name, api_host, api_port, api_user, api_pass, ssh_host, ssh_port, ssh_user, ssh_pass, gateway_flag, channel, cps, server_flag, active, weight
-- <?php t('Main Server'); ?>
INSERT INTO node VALUES (1, 'Main Server', 'localhost', '5038', 'myadmin', 'mysecret', 'localhost', '22', 'root', 'test', 7, 500, 50, 63, 1, 0);

/*==============================================================*/
/* Table: transmission                                          */
/* Desc: Table structure for table transmission                 */
/*==============================================================*/
CREATE TABLE transmission (
   transmission_id             int(11)                  not NULL auto_increment,
   title                       varchar(255)             default NULL,
   service_flag                int(11) unsigned         default NULL,
   account_id                  int(11)                  default NULL,
   contact_id                  int(11)                  default NULL,
   program_id                  int(11) unsigned         default NULL,
   origin                      varchar(128)             default NULL,
   direction                   varchar(128)             default NULL,
   status                      varchar(128)             default NULL,
   response                    varchar(255)             not NULL default '',
   try_allowed                 int(2)                   NOT NULL default 1,
   try_done                    int(2)                   NOT NULL default 0,
   last_run                    int(11)                  default NULL,
   is_deleted                  int(1)                   not NULL default 0,
   campaign_id                 int(11)                  default NULL,
   date_created                int(11)                  default NULL,
   created_by                  int(11) unsigned         default NULL,
   last_updated                int(11)                  default NULL,
   updated_by                  int(11) unsigned         default NULL,
  PRIMARY KEY (transmission_id)
) ENGINE = InnoDB;

/*==============================================================*/
/* Table: campaign                                              */
/* Desc: user can  create campaign*/
/*==============================================================*/
CREATE TABLE campaign
(
   campaign_id               int(11) unsigned       NOT NULL auto_increment,
   program_id                int(11)                NOT NULL,
   group_id                  int(11)                NOT NULL ,
   account_id                int(11)                default NULL,
   cpm                       int(11)                NOT NULL default 2,
   try_allowed               int(11)                NOT NULL default 1,
   contact_total             int(11)                NOT NULL default 0,
   contact_done              int(11)                NOT NULL default 0,
   status                    varchar(128)           NOT NULL default '',
   source                    varchar(128)           NOT NULL default '',
   pid                       varchar(128)           NOT NULL default '',
   last_run                  int(11)                default NULL,
   date_created              int(11)                default NULL,
   created_by                int(11)                default NULL,
   last_updated              int(11)                default NULL,
   updated_by                int(11) unsigned       default NULL,
   PRIMARY KEY (campaign_id)
) ENGINE = InnoDB;
CREATE INDEX campaign_created_by ON campaign (created_by);

DELIMITER |
CREATE TRIGGER transmission_insert AFTER INSERT
  ON transmission FOR EACH ROW BEGIN
    IF (NEW.campaign_id IS NOT NULL) THEN
     UPDATE campaign SET contact_total = (contact_total + 1) WHERE campaign_id = NEW.campaign_id;
    END IF;
  END;
|
CREATE TRIGGER transmission_update AFTER UPDATE
  ON transmission FOR EACH ROW BEGIN
    IF (OLD.status = 'pending' AND NEW.status != 'pending') THEN
      UPDATE campaign SET contact_done = (contact_done + 1) WHERE campaign_id = OLD.campaign_id;
    END IF;
  END;
|
CREATE TRIGGER transmission_delete AFTER DELETE
  ON transmission FOR EACH ROW BEGIN
    IF (OLD.status = 'pending') THEN
      UPDATE campaign SET contact_total = (contact_total - 1) WHERE campaign_id = OLD.campaign_id;
    ELSE
      UPDATE campaign SET contact_total = (contact_total - 1), contact_done = (contact_done - 1) WHERE campaign_id = OLD.campaign_id;
    END IF;
  END;
|
DELIMITER ;

/*==============================================================*/
/* Table: session                                               */
/* Desc: session data regarding each transmission               */
/*==============================================================*/
CREATE TABLE session
(
   session_id                     varchar(80)            default NULL,
   time_start                     int(11)                default 0,
   data                           text,
   PRIMARY KEY (session_id)
) ENGINE = InnoDB;

/*==============================================================*/
/* Table: spool                                                 */
/* Desc: this table provide store information related to each   */
/*       outgoing call and also hold pending calls.             */
/* TODO: handle / keep data of previous / multi attempts.       */
/*==============================================================*/
CREATE TABLE spool (
   spool_id                    int(11) unsigned         NOT NULL auto_increment,
   time_spool                  int(11)                  default NULL,
   time_start                  int(11)                  default NULL,
   time_connect                int(11)                  default NULL,
   time_end                    int(11)                  default NULL,
   call_id                     varchar(80)              NOT NULL default '',
   status                      varchar(80)              NOT NULL default '',
   response                    varchar(80)              NOT NULL default '',
   amount                      int(11)                  NOT NULL default 0,
   service_flag                int(11) unsigned         default NULL,
   transmission_id             int(11) unsigned         default NULL,
   provider_id                 int(11) unsigned         default NULL,
   node_id                     int(11) unsigned         default NULL,
   account_id                  int(11)                  default NULL,
   PRIMARY KEY (spool_id)
) ENGINE = InnoDB;
CREATE INDEX spool_transmission_id ON spool (transmission_id);
CREATE INDEX spool_account_id ON spool (account_id);
CREATE INDEX spool_time_end ON spool (time_end);

/*==============================================================*/
/* Table: spool_result                                          */
/* Desc: this table store response of prospect as per result of */
/*       spool call process.                                    */
/*==============================================================*/
CREATE TABLE spool_result (
   spool_result_id             int(11) unsigned         NOT NULL auto_increment,
   application_id              int(11)                  default NULL,
   type                        varchar(80)              NOT NULL default '',
   name                        varchar(80)              NOT NULL default '',
   data                        varchar(80)              NOT NULL default '',
   date_created                int(11)                  default NULL,
   spool_id                    int(11) unsigned         default NULL,
   PRIMARY KEY (spool_result_id)
) ENGINE = InnoDB;
CREATE INDEX spool_result_spool_id ON spool_result (spool_id);

/*==============================================================*/
/* Table: contact                                               */
/* Desc: user can upload contact lists here                     */
/*==============================================================*/
CREATE TABLE contact
(
   contact_id                    int(11) unsigned       NOT NULL auto_increment,
   first_name                    varchar(64)            default NULL,
   last_name                     varchar(64)            default NULL,
   phone                         varchar(32)            default NULL,
   email                         varchar(64)            default NULL,
   address                       varchar(128)           default NULL,
   custom1                       varchar(128)           default NULL,
   custom2                       varchar(128)           default NULL,
   custom3                       varchar(128)           default NULL,
   description                   varchar(255)           default NULL,
   date_created                  int(11)                default NULL,
   created_by                    int(11)                default NULL,
   last_updated                  int(11)                default NULL,
   updated_by                    int(11) unsigned       default NULL,
   PRIMARY KEY (contact_id)
) ENGINE = InnoDB;
CREATE INDEX contact_created_by ON contact (created_by);

/*==============================================================*/
/* Table: group                                                 */
/* Desc: user can create group                                  */
/*==============================================================*/
CREATE TABLE contact_group
(
   group_id                      int(11) unsigned       NOT NULL auto_increment,
   name                          varchar(128)           NOT NULL,
   description                   varchar(255)           NOT NULL default '',
   contact_total                 int(11)                NOT NULL default 0,
   date_created                  int(11)                default NULL,
   created_by                    int(11)                default NULL,
   last_updated                  int(11)                default NULL,
   updated_by                    int(11) unsigned       default NULL,
   PRIMARY KEY (group_id)
) ENGINE = InnoDB;

/*==============================================================*/
/* Table: group_contacts                                        */
/* Desc: link table for contact and group                       */
/*==============================================================*/
CREATE TABLE contact_link
(
   group_id                      int(11)                NOT NULL,
   contact_id                    int(11)                NOT NULL,
   PRIMARY KEY (group_id, contact_id)
) ENGINE = InnoDB;

DELIMITER |
CREATE TRIGGER contact_link_insert AFTER INSERT
  ON contact_link FOR EACH ROW BEGIN
    UPDATE contact_group SET contact_total = (contact_total + 1) WHERE group_id = NEW.group_id;
  END;
|
CREATE TRIGGER contact_link_delete AFTER DELETE
  ON contact_link FOR EACH ROW BEGIN
    UPDATE contact_group SET contact_total = (contact_total - 1) WHERE group_id = OLD.group_id;
  END;
|
DELIMITER ;

/*==============================================================*/
/* Table: ivr                                                   */
/*==============================================================*/

CREATE TABLE ivr
(
   ivr_id               int(11) unsigned       NOT NULL auto_increment,
   name                 varchar(128)           NOT NULL,
   description          varchar(255)           NOT NULL default '',
   data                 text,
   created              int(11) unsigned       default NULL,
   created_by           int(11) unsigned       default NULL,
   PRIMARY KEY (ivr_id)
) ENGINE = InnoDB;

/*==============================================================*/
/* Table: program                                               */
/* Desc: a list of program with pre defined data                */
/*==============================================================*/
CREATE TABLE program (
   program_id                  int(11) unsigned         NOT NULL auto_increment,
   name                        varchar(64)              NOT NULL default '',
   type                        varchar(64)              NOT NULL default '',
   data                        text,
   parent_id                   int(11) unsigned         default NULL,
   date_created                int(11)                  default NULL,
   created_by                  int(11)                  default NULL,
   last_updated                int(11)                  default NULL,
   updated_by                  int(11) unsigned         default NULL,
   PRIMARY KEY (program_id)
) ENGINE = InnoDB;

/*==============================================================*/
/* Table: program_resource                                      */
/* Desc: A link table between programs contacts and messages    */
/*==============================================================*/
CREATE TABLE program_resource (
   program_id                  int(11) unsigned         default NULL,
   resource_type               varchar(64)              NOT NULL default 'message',
   resource_id                 int(11) unsigned         default NULL
) ENGINE = InnoDB;
CREATE INDEX program_resource_resource_type ON program_resource (program_id, resource_type);

/*==============================================================*/
/* Table: application                                           */
/* Desc: application associated with program                    */
/*==============================================================*/
CREATE TABLE application (
   application_id              int(11) unsigned         NOT NULL auto_increment,
   name                        varchar(64)              NOT NULL default '',
   type                        varchar(64)              NOT NULL default '',
   data                        text,
   weight                      int(4)                   NOT NULL default 0,
   program_id                  int(11) unsigned         default NULL,
   PRIMARY KEY (application_id)
) ENGINE = InnoDB;
CREATE INDEX application_programe_id ON application (program_id);

/*==============================================================*/
/* Table: action                                                */
/* Desc: list of all available action for specific application  */
/*==============================================================*/
CREATE TABLE action (
   action_id                   int(11) unsigned         NOT NULL auto_increment,
   type                        varchar(128)             NOT NULL default '',
   action                      int(11)                  NOT NULL default 0,
   data                        varchar(64)              NOT NULL default '',
   weight                      int(4)                   NOT NULL default 0,
   is_default                  int(1)                   default 0,
   application_id              int(11) unsigned         default NULL,
   PRIMARY KEY (action_id)
) ENGINE = InnoDB;
CREATE INDEX action_application ON action (application_id);

/*==============================================================*/
/* Table: provider                                              */
/* Desc: All voip providers/gateways stored here                */
/*==============================================================*/
CREATE TABLE provider
(
   provider_id                   int(11) unsigned       NOT NULL auto_increment,
   name                          varchar(128)           NOT NULL default '',
   service_flag                  int(11) unsigned       default NULL,
   node_id                       int(11) unsigned       NOT NULL,
   host                          varchar(128)           NOT NULL default '',
   port                          int(6)                 NOT NULL default 5060,
   username                      varchar(128)           NOT NULL default '',
   password                      varchar(128)           NOT NULL default '',
   dialstring                    varchar(255)           NOT NULL default '',
   prefix                        varchar(255)           NOT NULL default '',
   settings                      text,
   description                   varchar(255)           NOT NULL default '',
   register                      varchar(255)           default NULL,
   weight                        int(11)                default 0,
   type                          varchar(32)            default NULL,
   active                        int(1)                 NOT NULL default 1,
   date_created                  int(11)                default NULL,
   created_by                    int(11)                default NULL,
   last_updated                  int(11)                default NULL,
   updated_by                    int(11) unsigned       default NULL,
   PRIMARY KEY (provider_id)
) ENGINE = InnoDB;
CREATE INDEX provider_created_by ON provider (created_by);

DELIMITER |
CREATE FUNCTION get_dialstring(Contact VARCHAR(32), Provider_ID INT(11))
  RETURNS VARCHAR(255)
  BEGIN
    DECLARE Prefix VARCHAR(255);
    DECLARE Prefix_Remove VARCHAR(255);
    DECLARE Prefix_Add VARCHAR(255);
    DECLARE Provider VARCHAR(255);
    DECLARE DialString VARCHAR(255);

    SELECT prefix INTO Prefix FROM provider WHERE provider_id=Provider_ID;
    SELECT name INTO Provider FROM provider WHERE provider_id=Provider_ID;
    SELECT dialstring INTO DialString FROM provider WHERE provider_id=Provider_ID;

    SET Contact = CONCAT(Prefix, Contact);
    SET DialString = REPLACE(DialString, '%provider', Provider);
    SET DialString = REPLACE(DialString, '%phone', Contact);

    RETURN DialString;
  END;
|
DELIMITER ;

/*==============================================================*/
/* Table: codec                                                 */
/* Desc: A list fo all voice codecs                             */
/*==============================================================*/
CREATE TABLE codec
(
   codec_id                      int(11) unsigned       NOT NULL auto_increment,
   codec_name                    varchar(128)           NOT NULL default '',
   codec_value                   varchar(128)           NOT NULL default '',
   codec_flag                    int(11)                NOT NULL default 0,
   active                        int(1)                 NOT NULL default 0,
   PRIMARY KEY (codec_id)
) ENGINE = InnoDB;

/*==============================================================*/
/* Desc: Dumping sample data into codec table                   */
/*==============================================================*/
INSERT INTO codec (codec_id, codec_value, codec_flag, active, codec_name) VALUES(1,  'g723',           1, 0, 'G.723.1');
INSERT INTO codec (codec_id, codec_value, codec_flag, active, codec_name) VALUES(2,  'gsm',            2, 1, 'GSM');
INSERT INTO codec (codec_id, codec_value, codec_flag, active, codec_name) VALUES(3,  'ulaw',           4, 1, 'G.711 u-law');
INSERT INTO codec (codec_id, codec_value, codec_flag, active, codec_name) VALUES(4,  'alaw',           8, 1, 'G.711 A-law');
INSERT INTO codec (codec_id, codec_value, codec_flag, active, codec_name) VALUES(5,  'g726aal2',      16, 0, 'G.726 AAL2');
INSERT INTO codec (codec_id, codec_value, codec_flag, active, codec_name) VALUES(6,  'adpcm',         32, 0, 'ADPCM');
INSERT INTO codec (codec_id, codec_value, codec_flag, active, codec_name) VALUES(7,  'slin',          64, 0, 'Slin');
INSERT INTO codec (codec_id, codec_value, codec_flag, active, codec_name) VALUES(8,  'lpc10',        128, 0, 'LPC10');
INSERT INTO codec (codec_id, codec_value, codec_flag, active, codec_name) VALUES(9,  'g729',         256, 0, 'G. 729A');
INSERT INTO codec (codec_id, codec_value, codec_flag, active, codec_name) VALUES(10, 'speex',        512, 0, 'SpeeX');
INSERT INTO codec (codec_id, codec_value, codec_flag, active, codec_name) VALUES(11, 'ilbc',        1024, 0, 'iLBC');
INSERT INTO codec (codec_id, codec_value, codec_flag, active, codec_name) VALUES(12, 'g726',        2048, 0, 'G.726 RFC3551');
INSERT INTO codec (codec_id, codec_value, codec_flag, active, codec_name) VALUES(13, 'g722',        4096, 0, 'G722');
INSERT INTO codec (codec_id, codec_value, codec_flag, active, codec_name) VALUES(17, 'jpeg',       65536, 0, 'JPEG image');
INSERT INTO codec (codec_id, codec_value, codec_flag, active, codec_name) VALUES(18, 'png',       131072, 0, 'PNG image');
INSERT INTO codec (codec_id, codec_value, codec_flag, active, codec_name) VALUES(19, 'h261',      262144, 0, 'H.261 Video');
INSERT INTO codec (codec_id, codec_value, codec_flag, active, codec_name) VALUES(20, 'h263',      524288, 0, 'H.263 Video');
INSERT INTO codec (codec_id, codec_value, codec_flag, active, codec_name) VALUES(21, 'h263p',    1048576, 0, 'H.263+ Video');
INSERT INTO codec (codec_id, codec_value, codec_flag, active, codec_name) VALUES(22, 'h264',     2097152, 0, 'H.264 Video');


/*==============================================================*/
/* Table: currency                                              */
/* Desc: World currencies with there three digit ID             */
/*==============================================================*/
CREATE TABLE currency
(
   currency_id                   varchar(3)             NOT NULL default '',
   name                          varchar(32)            NOT NULL default '',
   PRIMARY KEY (currency_id)
) ENGINE = InnoDB;

/*==============================================================*/
/* Desc: Dumping World currency data                            */
/*==============================================================*/
INSERT INTO currency VALUES('AED','United Arab Emirates dirham');
INSERT INTO currency VALUES('AFN','Afghan afghani');
INSERT INTO currency VALUES('ALL','Albanian lek');
INSERT INTO currency VALUES('AMD','Armenian dram');
INSERT INTO currency VALUES('ANG','Netherlands Antillean guilder');
INSERT INTO currency VALUES('AOA','Angolan kwanza');
INSERT INTO currency VALUES('ARS','Argentine peso');
INSERT INTO currency VALUES('AUD','Australian dollar');
INSERT INTO currency VALUES('AWG','Aruban florin');
INSERT INTO currency VALUES('AZN','Azerbaijani manat');
INSERT INTO currency VALUES('BAM','Bosnia and Herzegovina');
INSERT INTO currency VALUES('BBD','Barbados dollar');
INSERT INTO currency VALUES('BDT','Bangladeshi taka');
INSERT INTO currency VALUES('BGN','Bulgarian lev');
INSERT INTO currency VALUES('BHD','Bahraini dinar');
INSERT INTO currency VALUES('BIF','Burundian franc');
INSERT INTO currency VALUES('BMD','Bermudian dollar');
INSERT INTO currency VALUES('BND','Brunei dollar');
INSERT INTO currency VALUES('BOB','Boliviano');
INSERT INTO currency VALUES('BRL','Brazilian real');
INSERT INTO currency VALUES('BSD','Bahamian dollar');
INSERT INTO currency VALUES('BTN','Bhutanese ngultrum');
INSERT INTO currency VALUES('BWP','Botswana pula');
INSERT INTO currency VALUES('BYR','Belarusian ruble');
INSERT INTO currency VALUES('BZD','Belize dollar');
INSERT INTO currency VALUES('CAD','Canadian dollar');
INSERT INTO currency VALUES('CDF','Congolese franc');
INSERT INTO currency VALUES('CHF','Swiss franc');
INSERT INTO currency VALUES('CLP','Chilean peso');
INSERT INTO currency VALUES('CNY','Chinese yuan');
INSERT INTO currency VALUES('COP','Colombian peso');
INSERT INTO currency VALUES('CRC','Costa Rican colon');
INSERT INTO currency VALUES('CUP','Cuban peso');
INSERT INTO currency VALUES('CVE','Cape Verde escudo');
INSERT INTO currency VALUES('CZK','Czech koruna');
INSERT INTO currency VALUES('DJF','Djiboutian franc');
INSERT INTO currency VALUES('DKK','Danish krone');
INSERT INTO currency VALUES('DOP','Dominican peso');
INSERT INTO currency VALUES('DZD','Algerian dinar');
INSERT INTO currency VALUES('EEK','Estonian kroon');
INSERT INTO currency VALUES('EGP','Egyptian pound');
INSERT INTO currency VALUES('ERN','Eritrean nakfa');
INSERT INTO currency VALUES('ETB','Ethiopian birr');
INSERT INTO currency VALUES('EUR','Euro');
INSERT INTO currency VALUES('FJD','Fiji dollar');
INSERT INTO currency VALUES('FKP','Falkland Islands pound');
INSERT INTO currency VALUES('GBP','Pound sterling');
INSERT INTO currency VALUES('GGP','Guernsey pound');
INSERT INTO currency VALUES('GHS','Ghanaian cedi');
INSERT INTO currency VALUES('GIP','Gibraltar pound');
INSERT INTO currency VALUES('GMD','Gambian dalasi');
INSERT INTO currency VALUES('GNF','Guinean franc');
INSERT INTO currency VALUES('GTQ','Guatemalan quetzal');
INSERT INTO currency VALUES('GYD','Guyanese dollar');
INSERT INTO currency VALUES('HKD','Hong Kong dollar');
INSERT INTO currency VALUES('HNL','Honduran lempira');
INSERT INTO currency VALUES('HRK','Croatian kuna');
INSERT INTO currency VALUES('HTG','Haitian gourde');
INSERT INTO currency VALUES('HUF','Hungarian forint');
INSERT INTO currency VALUES('IDR','Indonesian rupiah');
INSERT INTO currency VALUES('ILS','Israeli new sheqel');
INSERT INTO currency VALUES('IMP','Isle of Man pound');
INSERT INTO currency VALUES('INR','Indian rupee');
INSERT INTO currency VALUES('IQD','Iraqi dinar');
INSERT INTO currency VALUES('IRR','Iranian rial');
INSERT INTO currency VALUES('ISK','Icelandic krona');
INSERT INTO currency VALUES('JEP','Jersey pound');
INSERT INTO currency VALUES('JMD','Jamaican dollar');
INSERT INTO currency VALUES('JOD','Jordanian dinar');
INSERT INTO currency VALUES('JPY','Japanese yen');
INSERT INTO currency VALUES('KES','Kenyan shilling');
INSERT INTO currency VALUES('KGS','Kyrgyzstani som');
INSERT INTO currency VALUES('KHR','Cambodian riel');
INSERT INTO currency VALUES('KMF','Comoro franc');
INSERT INTO currency VALUES('KPW','North Korean won');
INSERT INTO currency VALUES('KRW','South Korean won');
INSERT INTO currency VALUES('KWD','Kuwaiti dinar');
INSERT INTO currency VALUES('KYD','Cayman Islands dollar');
INSERT INTO currency VALUES('KZT','Kazakhstani tenge');
INSERT INTO currency VALUES('LAK','Lao kip');
INSERT INTO currency VALUES('LBP','Lebanese pound');
INSERT INTO currency VALUES('LKR','Sri Lanka rupee');
INSERT INTO currency VALUES('LRD','Liberian dollar');
INSERT INTO currency VALUES('LSL','Lesotho loti');
INSERT INTO currency VALUES('LTL','Lithuanian litas');
INSERT INTO currency VALUES('LVL','Latvian lats');
INSERT INTO currency VALUES('LYD','Libyan dinar');
INSERT INTO currency VALUES('MAD','Moroccan dirham');
INSERT INTO currency VALUES('MDL','Moldovan leu');
INSERT INTO currency VALUES('MGA','Malagasy ariary');
INSERT INTO currency VALUES('MKD','Macedonian denar');
INSERT INTO currency VALUES('MMK','Myanma kyat');
INSERT INTO currency VALUES('MNT','Mongolian tugrik');
INSERT INTO currency VALUES('MOP','Macanese pataca');
INSERT INTO currency VALUES('MRO','Mauritanian ouguiya');
INSERT INTO currency VALUES('MUR','Mauritian rupee');
INSERT INTO currency VALUES('MVR','Maldivian rufiyaa');
INSERT INTO currency VALUES('MWK','Malawian kwacha');
INSERT INTO currency VALUES('MXN','Mexican peso');
INSERT INTO currency VALUES('MYR','Malaysian ringgit');
INSERT INTO currency VALUES('MZN','Mozambican metical');
INSERT INTO currency VALUES('NAD','Namibian dollar');
INSERT INTO currency VALUES('NGN','Nigerian naira');
INSERT INTO currency VALUES('NIO','Cordoba oro');
INSERT INTO currency VALUES('NOK','Norwegian krone');
INSERT INTO currency VALUES('NPR','Nepalese rupee');
INSERT INTO currency VALUES('NZD','New Zealand dollar');
INSERT INTO currency VALUES('OMR','Omani rial');
INSERT INTO currency VALUES('PAB','Panamanian balboa');
INSERT INTO currency VALUES('PEN','Peruvian nuevo sol');
INSERT INTO currency VALUES('PGK','Papua New Guinean kina');
INSERT INTO currency VALUES('PHP','Philippine peso');
INSERT INTO currency VALUES('PKR','Pakistani rupee');
INSERT INTO currency VALUES('PLN','Polish zloty');
INSERT INTO currency VALUES('PYG','Paraguayan guarani');
INSERT INTO currency VALUES('QAR','Qatari rial');
INSERT INTO currency VALUES('RON','Romanian new leu');
INSERT INTO currency VALUES('RSD','Serbian dinar');
INSERT INTO currency VALUES('RUB','Russian rouble');
INSERT INTO currency VALUES('RWF','Rwandan franc');
INSERT INTO currency VALUES('SAR','Saudi riyal');
INSERT INTO currency VALUES('SBD','Solomon Islands dollar');
INSERT INTO currency VALUES('SCR','Seychelles rupee');
INSERT INTO currency VALUES('SDG','Sudanese pound');
INSERT INTO currency VALUES('SEK','Swedish krona/kronor');
INSERT INTO currency VALUES('SGD','Singapore dollar');
INSERT INTO currency VALUES('SHP','Saint Helena pound');
INSERT INTO currency VALUES('SLL','Sierra Leonean leone');
INSERT INTO currency VALUES('SLS','Somaliland shilling');
INSERT INTO currency VALUES('SRD','Surinamese dollar');
INSERT INTO currency VALUES('STD','Sao Tome and Principe dobra');
INSERT INTO currency VALUES('SYP','Syrian pound');
INSERT INTO currency VALUES('SZL','Lilangeni');
INSERT INTO currency VALUES('THB','Thai baht');
INSERT INTO currency VALUES('TJS','Tajikistani somoni');
INSERT INTO currency VALUES('TMT','Turkmenistani manat');
INSERT INTO currency VALUES('TND','Tunisian dinar');
INSERT INTO currency VALUES('TOP','Tongan pa anga');
INSERT INTO currency VALUES('TRY','Turkish lira');
INSERT INTO currency VALUES('TTD','Trinidad and Tobago dollar');
INSERT INTO currency VALUES('TWD','New Taiwan dollar');
INSERT INTO currency VALUES('TZS','Tanzanian shilling');
INSERT INTO currency VALUES('UAH','Ukrainian hryvnia');
INSERT INTO currency VALUES('UGX','Ugandan shilling');
INSERT INTO currency VALUES('USD','United States dollar');
INSERT INTO currency VALUES('UYU','Uruguayan peso');
INSERT INTO currency VALUES('UZS','Uzbekistan som');
INSERT INTO currency VALUES('VEF','Venezuelan bolivar fuerte');
INSERT INTO currency VALUES('VND','Vietnamese dong');
INSERT INTO currency VALUES('VUV','Vanuatu vatu');
INSERT INTO currency VALUES('WST','Samoan tala');
INSERT INTO currency VALUES('XAF','CFA franc BEAC');
INSERT INTO currency VALUES('XAG','Silver');
INSERT INTO currency VALUES('XAU','Gold');
INSERT INTO currency VALUES('XCD','East Caribbean dollar');
INSERT INTO currency VALUES('XOF','CFA Franc BCEAO');
INSERT INTO currency VALUES('XPD','Palladium');
INSERT INTO currency VALUES('XPF','CFP franc');
INSERT INTO currency VALUES('XPT','Platinum');
INSERT INTO currency VALUES('YER','Yemeni rial');
INSERT INTO currency VALUES('ZAR','South African rand');
INSERT INTO currency VALUES('ZMK','Zambian kwacha');
INSERT INTO currency VALUES('ZWL','Zimbabwe dollar');

/*==============================================================*/
/* Table: language                                              */
/* Desc: List of world languages with there two digit ID        */
/*==============================================================*/
CREATE TABLE language
(
   language_id                   varchar(2)             NOT NULL default '',
   active                        int(1)                 NOT NULL default 0,
   name                          varchar(32)            NOT NULL default '',
   PRIMARY KEY (language_id)
) ENGINE = InnoDB
DEFAULT CHARSET = utf8;

/*==============================================================*/
/* Desc: Dumping List of wolrd languages into language table    */
/*==============================================================*/
SET NAMES utf8;
INSERT INTO language VALUES('aa',0,'Afar');
INSERT INTO language VALUES('ab',0,'Abkhazian');
INSERT INTO language VALUES('af',0,'Afrikaans');
INSERT INTO language VALUES('am',0,'Amharic');
INSERT INTO language VALUES('ar',1,'العربية');
INSERT INTO language VALUES('as',0,'Assamese');
INSERT INTO language VALUES('ay',0,'Aymara');
INSERT INTO language VALUES('az',0,'Azerbaijani');
INSERT INTO language VALUES('ba',0,'Bashkir');
INSERT INTO language VALUES('be',0,'Byelorussian');
INSERT INTO language VALUES('bg',0,'Bulgarian');
INSERT INTO language VALUES('bh',0,'Bihari');
INSERT INTO language VALUES('bi',0,'Bislama');
INSERT INTO language VALUES('bn',0,'Bengali,0, Bangla');
INSERT INTO language VALUES('bo',0,'Tibetan');
INSERT INTO language VALUES('br',0,'Breton');
INSERT INTO language VALUES('ca',0,'Catalan');
INSERT INTO language VALUES('co',0,'Corsican');
INSERT INTO language VALUES('cs',0,'Czech');
INSERT INTO language VALUES('cy',0,'Welsh');
INSERT INTO language VALUES('da',0,'Danish');
INSERT INTO language VALUES('de',0,'German');
INSERT INTO language VALUES('dz',0,'Bhutani');
INSERT INTO language VALUES('el',0,'Greek');
INSERT INTO language VALUES('en',1,'English, American');
INSERT INTO language VALUES('eo',0,'Esperanto');
INSERT INTO language VALUES('es',1,'español');
INSERT INTO language VALUES('et',0,'Estonian');
INSERT INTO language VALUES('eu',0,'Basque');
INSERT INTO language VALUES('fa',0,'Persian');
INSERT INTO language VALUES('fi',0,'Finnish');
INSERT INTO language VALUES('fj',0,'Fiji');
INSERT INTO language VALUES('fo',0,'Faeroese');
INSERT INTO language VALUES('fr',1,'française');
INSERT INTO language VALUES('fy',0,'Frisian');
INSERT INTO language VALUES('ga',0,'Irish');
INSERT INTO language VALUES('gd',0,'Gaelic ("Scots Gaelic")');
INSERT INTO language VALUES('gl',0,'Galician');
INSERT INTO language VALUES('gn',0,'Guarani');
INSERT INTO language VALUES('gu',0,'Gujarati');
INSERT INTO language VALUES('ha',0,'Hausa');
INSERT INTO language VALUES('hi',0,'Hindi');
INSERT INTO language VALUES('hr',0,'Croatian');
INSERT INTO language VALUES('hu',0,'Hungarian');
INSERT INTO language VALUES('hy',0,'Armenian');
INSERT INTO language VALUES('ia',0,'Interlingua');
INSERT INTO language VALUES('ie',0,'Interlingue');
INSERT INTO language VALUES('ik',0,'Inupiak');
INSERT INTO language VALUES('in',0,'Indonesian');
INSERT INTO language VALUES('is',0,'Icelandic');
INSERT INTO language VALUES('it',1,'Italiano');
INSERT INTO language VALUES('iw',0,'Hebrew');
INSERT INTO language VALUES('ja',0,'Japanese');
INSERT INTO language VALUES('ji',0,'Yiddish');
INSERT INTO language VALUES('jw',0,'Javanese');
INSERT INTO language VALUES('ka',0,'Georgian');
INSERT INTO language VALUES('kk',0,'Kazakh');
INSERT INTO language VALUES('kl',0,'Greenlandic');
INSERT INTO language VALUES('km',0,'Cambodian');
INSERT INTO language VALUES('kn',0,'Kannada');
INSERT INTO language VALUES('ko',0,'Korean');
INSERT INTO language VALUES('ks',0,'Kashmiri');
INSERT INTO language VALUES('ku',0,'Kurdish');
INSERT INTO language VALUES('ky',0,'Kirghiz');
INSERT INTO language VALUES('la',0,'Latin');
INSERT INTO language VALUES('ln',0,'Lingala');
INSERT INTO language VALUES('lo',0,'Laothian');
INSERT INTO language VALUES('lt',0,'Lithuanian');
INSERT INTO language VALUES('lv',0,'Latvian, Lettish');
INSERT INTO language VALUES('mg',0,'Malagasy');
INSERT INTO language VALUES('mi',0,'Maori');
INSERT INTO language VALUES('mk',0,'Macedonian');
INSERT INTO language VALUES('ml',0,'Malayalam');
INSERT INTO language VALUES('mn',0,'Mongolian');
INSERT INTO language VALUES('mo',0,'Moldavian');
INSERT INTO language VALUES('mr',0,'Marathi');
INSERT INTO language VALUES('ms',0,'Malay');
INSERT INTO language VALUES('mt',0,'Maltese');
INSERT INTO language VALUES('my',0,'Burmese');
INSERT INTO language VALUES('na',0,'Nauru');
INSERT INTO language VALUES('ne',0,'Nepali');
INSERT INTO language VALUES('nl',0,'Dutch');
INSERT INTO language VALUES('no',0,'Norwegian');
INSERT INTO language VALUES('oc',0,'Occitan');
INSERT INTO language VALUES('om',0,'Oromo, Afan');
INSERT INTO language VALUES('or',0,'Oriya');
INSERT INTO language VALUES('pa',0,'Punjabi');
INSERT INTO language VALUES('pl',0,'Polish');
INSERT INTO language VALUES('ps',0,'Pashto, Pushto');
INSERT INTO language VALUES('pt',1,'Português');
INSERT INTO language VALUES('qu',0,'Quechua');
INSERT INTO language VALUES('rm',0,'Rhaeto-Romance');
INSERT INTO language VALUES('rn',0,'Kirundi');
INSERT INTO language VALUES('ro',0,'Romanian');
INSERT INTO language VALUES('ru',1,'русский');
INSERT INTO language VALUES('rw',0,'Kinyarwanda');
INSERT INTO language VALUES('sa',0,'Sanskrit');
INSERT INTO language VALUES('sd',0,'Sindhi');
INSERT INTO language VALUES('sg',0,'Sangro');
INSERT INTO language VALUES('sh',0,'Serbo-Croatian');
INSERT INTO language VALUES('si',0,'Singhalese');
INSERT INTO language VALUES('sk',0,'Slovak');
INSERT INTO language VALUES('sl',0,'Slovenian');
INSERT INTO language VALUES('sm',0,'Samoan');
INSERT INTO language VALUES('sn',0,'Shona');
INSERT INTO language VALUES('so',0,'Somali');
INSERT INTO language VALUES('sq',0,'Albanian');
INSERT INTO language VALUES('sr',0,'Serbian');
INSERT INTO language VALUES('ss',0,'Siswati');
INSERT INTO language VALUES('st',0,'Sesotho');
INSERT INTO language VALUES('su',0,'Sudanese');
INSERT INTO language VALUES('sv',0,'Swedish');
INSERT INTO language VALUES('sw',0,'Swahili');
INSERT INTO language VALUES('ta',0,'Tamil');
INSERT INTO language VALUES('te',0,'Tegulu');
INSERT INTO language VALUES('tg',0,'Tajik');
INSERT INTO language VALUES('th',0,'Thai');
INSERT INTO language VALUES('ti',0,'Tigrinya');
INSERT INTO language VALUES('tk',0,'Turkmen');
INSERT INTO language VALUES('tl',0,'Tagalog');
INSERT INTO language VALUES('tn',0,'Setswana');
INSERT INTO language VALUES('to',0,'Tonga');
INSERT INTO language VALUES('tr',0,'Turkish');
INSERT INTO language VALUES('ts',0,'Tsonga');
INSERT INTO language VALUES('tt',0,'Tatar');
INSERT INTO language VALUES('tw',0,'Twi');
INSERT INTO language VALUES('uk',0,'Ukrainian');
INSERT INTO language VALUES('ur',0,'Urdu');
INSERT INTO language VALUES('uz',0,'Uzbek');
INSERT INTO language VALUES('vi',0,'Vietnamese');
INSERT INTO language VALUES('vo',0,'Volapuk');
INSERT INTO language VALUES('wo',0,'Wolof');
INSERT INTO language VALUES('xh',0,'Xhosa');
INSERT INTO language VALUES('yo',0,'Yoruba');
INSERT INTO language VALUES('zh',1,'Chinese');
INSERT INTO language VALUES('zu',0,'Zulu');
SET NAMES DEFAULT;

/*==============================================================*/
/* Table: timezone                                              */
/* Desc: A distinct selection of diffirenct timezones with      */
/*       Actuall offset in seconds                              */
/*==============================================================*/
CREATE TABLE timezone
(
   timezone_id                   int(11)                NOT NULL default 0,
   name                          varchar(16)            NOT NULL default '',
   PRIMARY KEY (timezone_id)
) ENGINE = InnoDB;

/*==============================================================*/
/* Desc: Dumping world timezone data                            */
/*==============================================================*/
INSERT INTO timezone VALUES (-39600, 'GMT -11:00');
INSERT INTO timezone VALUES (-36000, 'GMT -10:00');
INSERT INTO timezone VALUES (-34200, 'GMT -09:30');
INSERT INTO timezone VALUES (-32400, 'GMT -09:00');
INSERT INTO timezone VALUES (-28800, 'GMT -08:00');
INSERT INTO timezone VALUES (-25200, 'GMT -07:00');
INSERT INTO timezone VALUES (-21600, 'GMT -06:00');
INSERT INTO timezone VALUES (-18000, 'GMT -05:00');
INSERT INTO timezone VALUES (-16200, 'GMT -04:30');
INSERT INTO timezone VALUES (-14400, 'GMT -04:00');
INSERT INTO timezone VALUES (-12600, 'GMT -03:30');
INSERT INTO timezone VALUES (-10800, 'GMT -03:00');
INSERT INTO timezone VALUES (-9000, 'GMT -02:30');
INSERT INTO timezone VALUES (-7200, 'GMT -02:00');
INSERT INTO timezone VALUES (-3600, 'GMT -01:00');
INSERT INTO timezone VALUES (0, 'GMT');
INSERT INTO timezone VALUES (3600 , 'GMT +01:00');
INSERT INTO timezone VALUES (7200 , 'GMT +02:00');
INSERT INTO timezone VALUES (10800, 'GMT +03:00');
INSERT INTO timezone VALUES (12600, 'GMT +03:30');
INSERT INTO timezone VALUES (14400, 'GMT +04:00');
INSERT INTO timezone VALUES (18000, 'GMT +05:00');
INSERT INTO timezone VALUES (19800, 'GMT +05:30');
INSERT INTO timezone VALUES (20700, 'GMT +05:45');
INSERT INTO timezone VALUES (21600, 'GMT +06:00');
INSERT INTO timezone VALUES (23400, 'GMT +06:30');
INSERT INTO timezone VALUES (25200, 'GMT +07:00');
INSERT INTO timezone VALUES (28800, 'GMT +08:00');
INSERT INTO timezone VALUES (32400, 'GMT +09:00');
INSERT INTO timezone VALUES (34200, 'GMT +09:30');
INSERT INTO timezone VALUES (36000, 'GMT +10:00');
INSERT INTO timezone VALUES (37800, 'GMT +10:30');
INSERT INTO timezone VALUES (39600, 'GMT +11:00');
INSERT INTO timezone VALUES (41400, 'GMT +11:30');
INSERT INTO timezone VALUES (43200, 'GMT +12:00');
INSERT INTO timezone VALUES (45900, 'GMT +12:45');
INSERT INTO timezone VALUES (46800, 'GMT +13:00');
INSERT INTO timezone VALUES (50400, 'GMT +14:00');

/*==============================================================*/
/* Table: region                                                */
/* Desc: List of continents of world to arrange countries       */
/*==============================================================*/
CREATE TABLE region
(
   region_id                     varchar(5)             NOT NULL default '',
   name                          varchar(32)            NOT NULL default '',
   PRIMARY KEY (region_id)
) ENGINE = InnoDB;

/*==============================================================*/
/* Desc: Dumping continent / region data                        */
/*==============================================================*/
INSERT INTO region VALUES('af', 'Africa');
INSERT INTO region VALUES('af.e', 'Eastern Africa');
INSERT INTO region VALUES('af.m', 'Middle Africa');
INSERT INTO region VALUES('af.n', 'Northern Africa');
INSERT INTO region VALUES('af.s', 'Southern Africa');
INSERT INTO region VALUES('af.w', 'Western Africa');
INSERT INTO region VALUES('am', 'America');
INSERT INTO region VALUES('am.ca', 'Caribbean');
INSERT INTO region VALUES('am.c', 'Central America');
INSERT INTO region VALUES('am.s', 'South America');
INSERT INTO region VALUES('am.n', 'Northern America');
INSERT INTO region VALUES('as', 'Asia');
INSERT INTO region VALUES('as.c', 'Central Asia');
INSERT INTO region VALUES('as.e', 'Eastern Asia');
INSERT INTO region VALUES('as.s', 'Southern Asia');
INSERT INTO region VALUES('as.se', 'South-Eastern Asia');
INSERT INTO region VALUES('as.w', 'Western Asia');
INSERT INTO region VALUES('e', 'Europe');
INSERT INTO region VALUES('e.e', 'Eastern Europe');
INSERT INTO region VALUES('e.n', 'Northern Europe');
INSERT INTO region VALUES('e.s', 'Southern Europe');
INSERT INTO region VALUES('e.w', 'Western Europe');
INSERT INTO region VALUES('o', 'Oceania');
INSERT INTO region VALUES('o.a', 'Australia and New Zealand');
INSERT INTO region VALUES('o.me', 'Melanesia');
INSERT INTO region VALUES('o.mi', 'Micronesia');
INSERT INTO region VALUES('o.p', 'Polynesia');
INSERT INTO region VALUES('u', 'Unknown');

/*==============================================================*/
/* Table: country                                               */
/* Desc: this table will hold list of countries along with      */
/*       dialing code, idd, ldd, timezone, language, region,    */
/*       currency, iso code etc                                 */
/*==============================================================*/
CREATE TABLE country
(
   country_id                    int(11) unsigned       NOT NULL auto_increment,
   name                          varchar(64)            NOT NULL,
   iso_code_2                    varchar(2)             default NULL,
   iso_code_3                    varchar(3)             default NULL,
   dialing_code                  text,
   ndd                           varchar(6)             default NULL,
   idd                           varchar(6)             default NULL,
   locallenght                   int(2)                 default NULL,
   timezone_id                   int(11)                default NULL,
   timezone_dst                  int(1)                 default 0,
   language_id                   varchar(2)             default NULL,
   currency_id                   varchar(3)             default NULL,
   region_id                     varchar(5)             default NULL,
   date_created                  int(11)                default NULL,
   created_by                    int(11)                default NULL,
   last_updated                  int(11)                default NULL,
   updated_by                    int(11) unsigned       default NULL,
   PRIMARY KEY (country_id)
) ENGINE = InnoDB;
CREATE INDEX country_region_id ON country (region_id(5));

/*==============================================================*/
/* Desc: Dumping list of all world countries                    */
/*==============================================================*/
INSERT INTO country VALUES(4, 'Afghanistan', 'AF', 'AFG', 93, '0', '00', NULL, 16200, 0, 'FA', 'AFN', 'as.s', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(8, 'Albania', 'AL', 'ALB', 355, '0', '00', NULL, 3600, 0, 'SQ', 'ALL', 'e.s', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(10, 'Antarctica', 'AQ', 'ATA', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(12, 'Algeria', 'DZ', 'DZA', 213, '7', '00', NULL, 0, 0, 'AR', 'DZD', 'af.n', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(16, 'American Samoa', 'AS', 'ASM', 1684, NULL, NULL, NULL, -39600, 0, 'EN', 'USD', 'o.p', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(20, 'Andorra', 'AD', 'AND', 376, '', '00', NULL, 3600, 0, 'CA', 'EUR', 'e.s', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(24, 'Angola', 'AO', 'AGO', 244, '0', '00', NULL, 3600, 0, 'KG', 'AOA', 'af.m', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(28, 'Antigua and Barbuda', 'AG', 'ATG', 1268, '1', '011', NULL, -14400, 0, 'EN', 'XCD', 'am.ca', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(31, 'Azerbaijan', 'AZ', 'AZE', 994, '0', '00', NULL, 14400, 0, 'AV', 'AZN', 'as.w', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(32, 'Argentina', 'AR', 'ARG', 54, '0', '00', NULL, -10800, 0, 'CY', 'ARS', 'am.s', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(36, 'Australia', 'AU', 'AUS', 61, '', '0011', NULL, 36000, 0, 'EN', 'AUD', 'o.a', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(40, 'Austria', 'AT', 'AUT', 43, '0', '00', NULL, 3600, 0, 'DE', 'EUR', 'e.w', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(44, 'Bahamas', 'BS', 'BHS', 1242, '1', '011', NULL, -18000, 0, 'EN', 'BSD', 'am.ca', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(48, 'Bahrain', 'BH', 'BHR', 973, '', '00', NULL, 10800, 0, 'AR', 'BHD', 'as.w', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(50, 'Bangladesh', 'BD', 'BGD', 880, '0', '00', NULL, 21600, 0, 'BN', 'BDT', 'as.s', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(51, 'Armenia', 'AM', 'ARM', 374, '0', '00', NULL, 14400, 0, 'HY', 'AMD', 'as.w', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(52, 'Barbados', 'BB', 'BRB', 1246, NULL, NULL, NULL, -14400, 0, 'EN', 'BBD', 'am.ca', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(56, 'Belgium', 'BE', 'BEL', 32, '0', '00', NULL, 3600, 0, 'DE', 'EUR', 'e.w', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(60, 'Bermuda', 'BM', 'BMU', 1441, '1', '011', NULL, -14400, 0, 'EN', 'BMD', 'am.n', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(64, 'Bhutan', 'BT', 'BTN', 975, '', '00', NULL, 19800, 0, 'DZ', 'BTN', 'as.s', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(68, 'Bolivia, Plurinational State of', 'BO', 'BOL', 591, '0', '00', NULL, -14400, 0, 'AY', 'BOB', 'am.s', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(70, 'Bosnia and Herzegovina', 'BA', 'BIH', 387, '0', '00', NULL, 3600, 0, 'BS', 'BAM', 'e.s', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(72, 'Botswana', 'BW', 'BWA', 267, '', '00', NULL, 7200, 0, 'EN', 'BWP', 'af.s', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(74, 'Bouvet Island', 'BV', 'BVT', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(76, 'Brazil', 'BR', 'BRA', 55, '0', '00', NULL, -10800, 0, 'PT', 'BRL', 'am.s', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(84, 'Belize', 'BZ', 'BLZ', 501, '0', '00', NULL, -21600, 0, 'EN', 'BZD', 'am.c', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(86, 'British Indian Ocean Territory', 'IO', 'IOT', 246, '', '00', NULL, -14400, 0, 'EN', 'GBP', '', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(90, 'Solomon Islands', 'SB', 'SLB', 677, '', '00', NULL, 39600, 0, 'EN', 'SBD', 'o.me', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(92, 'Virgin Islands, British', 'VG', 'VGB', 1284, '1', '011', NULL, -14400, 0, 'EN', 'USD', 'am.ca', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(96, 'Brunei Darussalam', 'BN', 'BRN', 673, '0', '00', NULL, 28800, 0, 'EN', 'BND', 'as.se', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(100, 'Bulgaria', 'BG', 'BGR', 359, '0', '00', NULL, 7200, 0, 'BG', 'BGN', 'e.e', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(104, 'Myanmar', 'MM', 'MMR', 95, '', '00', NULL, 23400, 0, 'MY', 'MMK', 'as.se', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(108, 'Burundi', 'BI', 'BDI', 257, '', '90', NULL, 7200, 0, 'FR', 'BIF', 'af.e', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(112, 'Belarus', 'BY', 'BLR', 375, '8', '8~10', NULL, 10800, 0, 'BE', 'BYR', 'e.e', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(116, 'Cambodia', 'KH', 'KHM', 855, '0', '001', NULL, 25200, 0, 'KM', 'KHR', 'as.se', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(120, 'Cameroon', 'CM', 'CMR', 237, '', '00', NULL, 3600, 0, 'EN', 'XAF', 'af.m', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(124, 'Canada', 'CA', 'CAN', 1, '1', '011', NULL, -14400, 0, 'CR', 'CAD', 'am.n', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(132, 'Cape Verde', 'CV', 'CPV', 238, '', '0', NULL, -3600, 0, 'PT', 'CVE', 'af.w', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(136, 'Cayman Islands', 'KY', 'CYM', 1345, '1', '011', NULL, -18000, 0, 'EN', 'KYD', 'am.ca', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(140, 'Central African Republic', 'CF', 'CAF', 236, '', '00', NULL, 3600, 0, 'FR', 'XAF', 'af.m', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(144, 'Sri Lanka', 'LK', 'LKA', 94, '0', '00', NULL, 19800, 0, 'SI', 'LKR', 'as.s', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(148, 'Chad', 'TD', 'TCD', 235, '', '15', NULL, 3600, 0, 'AR', 'XAF', 'af.m', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(152, 'Chile', 'CL', 'CHL', 56, '0', '00', NULL, -14400, 0, 'AY', 'CLP', 'am.s', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(156, 'China', 'CN', 'CHN', 86, '0', '00', NULL, 28800, 0, 'BO', 'CNY', 'as.e', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(158, 'Taiwan, Province of China', 'TW', 'TWN', 886, '', '002', NULL, 28800, 0, 'ZH', 'TWD', 'as.e', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(162, 'Christmas Island', 'CX', 'CXR', 61, '', '0011', NULL, 36000, 0, 'MS', 'AUD', '', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(166, 'Cocos (Keeling) Islands', 'CC', 'CCK', 61, '', '0011', NULL, 36000, 0, 'MS', 'AUD', '', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(170, 'Colombia', 'CO', 'COL', 57, '09', '009', NULL, -18000, 0, 'ES', 'COP', 'am.s', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(174, 'Comoros', 'KM', 'COM', 269, '', '00', NULL, 10800, 0, 'FR', 'KMF', 'af.e', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(175, 'Mayotte', 'YT', 'MYT', 262, '0', '00', NULL, 14400, 0, 'FR', 'EUR', 'af.e', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(178, 'Congo', 'CG', 'COG', 242, '', '00', NULL, -18000, 0, 'FR', 'XAF', 'af.m', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(180, 'Congo, the Democratic Republic of the', 'CD', 'COD', 243, '', '00', NULL, 7200, 0, 'FR', 'CDF', 'af.m', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(184, 'Cook Islands', 'CK', 'COK', 682, '00', '00', NULL, -36000, 0, 'EN', 'NZD', 'o.p', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(188, 'Costa Rica', 'CR', 'CRI', 506, '', '00', NULL, -21600, 0, 'ES', 'CRC', 'am.c', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(191, 'Croatia', 'HR', 'HRV', 385, '0', '00', NULL, 3600, 0, 'HR', 'HRK', 'e.s', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(192, 'Cuba', 'CU', 'CUB', 53, '0', '119', NULL, -10800, 0, 'ES', 'CUP', 'am.ca', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(196, 'Cyprus', 'CY', 'CYP', 90392, NULL, NULL, NULL, 7200, 0, 'EL', 'EUR', 'as.w', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(203, 'Czech Republic', 'CZ', 'CZE', 420, '', '00', NULL, 3600, 0, 'CS', 'CZK', 'e.e', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(204, 'Benin', 'BJ', 'BEN', 229, '', '00', NULL, 3600, 0, 'FR', 'XOF', 'af.w', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(208, 'Denmark', 'DK', 'DNK', 45, '', '00', NULL, 3600, 0, 'DA', 'DKK', 'e.n', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(212, 'Dominica', 'DM', 'DMA', 1767, '', '011', NULL, -14400, 0, 'EN', 'XCD', 'am.ca', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(214, 'Dominican Republic', 'DO', 'DOM', 1809, NULL, NULL, NULL, -14400, 0, 'ES', 'DOP', 'am.ca', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(218, 'Ecuador', 'EC', 'ECU', 593, '0', '00', NULL, -18000, 0, 'ES', 'USD', 'am.s', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(222, 'El Salvador', 'SV', 'SLV', 503, '', '00', NULL, -21600, 0, 'ES', 'USD', 'am.c', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(226, 'Equatorial Guinea', 'GQ', 'GNQ', 240, '', '00', NULL, 3600, 0, 'ES', 'XAF', 'af.m', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(231, 'Ethiopia', 'ET', 'ETH', 251, '0', '00', NULL, 10800, 0, 'AA', 'ETB', 'af.e', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(232, 'Eritrea', 'ER', 'ERI', 291, '', '00', NULL, 10800, 0, 'AA', 'ERN', 'af.e', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(233, 'Estonia', 'EE', 'EST', 372, '', '00', NULL, 10800, 0, 'ET', 'EEK', 'e.n', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(234, 'Faroe Islands', 'FO', 'FRO', 298, '', '00', NULL, 0, 0, 'DA', 'DKK', 'e.n', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(238, 'Falkland Islands (Malvinas)', 'FK', 'FLK', 500, '', '00', NULL, -14400, 0, 'EN', 'FKP', 'am.s', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(239, 'South Georgia and the South Sandwich Islands', 'GS', 'SGS', NULL, NULL, NULL, NULL, -7200, 0, 'EN', 'GBP', NULL, 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(242, 'Fiji', 'FJ', 'FJI', 679, '', '00', NULL, 43200, 0, 'EN', 'FJD', 'o.me', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(246, 'Finland', 'FI', 'FIN', 358, '0', '00', NULL, 7200, 0, 'FI', 'EUR', 'e.n', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(248, 'Aland Islands', 'AX', 'ALA', 35818, NULL, NULL, NULL, 7200, 0, 'SV', 'EUR', 'e.n', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(250, 'France', 'FR', 'FRA', 33, '', '00', NULL, 3600, 0, 'BR', 'EUR', 'e.w', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(254, 'French Guiana', 'GF', 'GUF', 594, '', '00', NULL, -14400, 0, 'FR', 'EUR', 'am.s', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(258, 'French Polynesia', 'PF', 'PYF', 689, '', '00', NULL, -36000, 0, 'FR', 'XPF', 'o.p', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(260, 'French Southern Territories', 'TF', 'ATF', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(262, 'Djibouti', 'DJ', 'DJI', 253, '', '00', NULL, 10800, 0, 'AA', 'DJF', 'af.e', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(266, 'Gabon', 'GA', 'GAB', 241, '', '00', NULL, 3600, 0, 'FR', 'XAF', 'af.m', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(268, 'Georgia', 'GE', 'GEO', 995, '8', '8~10', NULL, 14400, 0, 'AB', 'RUB', 'as.w', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(270, 'Gambia', 'GM', 'GMB', 220, '', '00', NULL, 0, 0, 'BM', 'GMD', 'af.w', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(275, 'Palestinian Territory, Occupied', 'PS', 'PSE', 970, '0', '00', NULL, 7200, 0, 'AR', 'ILS', 'as.w', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(276, 'Germany', 'DE', 'DEU', 49, '0', '00', NULL, 3600, 0, 'DA', 'EUR', 'e.w', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(288, 'Ghana', 'GH', 'GHA', 233, '', '00', NULL, 0, 0, 'AK', 'GHS', 'af.w', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(292, 'Gibraltar', 'GI', 'GIB', 350, '', '00', NULL, 3600, 0, 'EN', 'GIP', 'e.s', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(296, 'Kiribati', 'KI', 'KIR', 686, '0', '00', NULL, 43200, 0, 'EN', 'AUD', 'o.mi', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(300, 'Greece', 'GR', 'GRC', 30, '', '00', NULL, 7200, 0, 'EL', 'EUR', 'e.s', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(304, 'Greenland', 'GL', 'GRL', 299, '', '00', NULL, -10800, 0, 'DA', 'DKK', 'am.n', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(308, 'Grenada', 'GD', 'GRD', 1473, '4', '011', NULL, -14400, 0, 'EN', 'XCD', 'am.ca', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(312, 'Guadeloupe', 'GP', 'GLP', 590, '', '00', NULL, -14400, 0, 'FR', 'EUR', 'am.ca', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(316, 'Guam', 'GU', 'GUM', 1671, '1', '011', NULL, 36000, 0, 'CH', 'USD', 'o.mi', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(320, 'Guatemala', 'GT', 'GTM', 502, '', '00', NULL, -21600, 0, 'ES', 'GTQ', 'am.c', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(324, 'Guinea', 'GN', 'GIN', 224, '0', '00', NULL, 0, 0, 'FR', 'GNF', 'af.w', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(328, 'Guyana', 'GY', 'GUY', 592, '0', '001', NULL, -10800, 0, 'EN', 'GYD', 'am.s', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(332, 'Haiti', 'HT', 'HTI', 509, '0', '00', NULL, -18000, 0, 'FR', 'HTG', 'am.ca', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(334, 'Heard Island and McDonald Islands', 'HM', 'HMD', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(336, 'Holy See (Vatican City State)', 'VA', 'VAT', 379, '', '00', NULL, 3600, 0, 'FR', 'EUR', 'e.s', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(340, 'Honduras', 'HN', 'HND', 504, '0', '00', NULL, -21600, 0, 'ES', 'HNL', 'am.c', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(344, 'Hong Kong', 'HK', 'HKG', 852, '', '001', NULL, 28800, 0, 'EN', 'HKD', 'as.e', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(348, 'Hungary', 'HU', 'HUN', 36, '06', '00', NULL, 3600, 0, 'DE', 'HUF', 'e.e', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(352, 'Iceland', 'IS', 'ISL', 354, '0', '00', NULL, 0, 0, 'IS', 'ISK', 'e.n', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(356, 'India', 'IN', 'IND', 91, '0', '00', NULL, 19800, 0, 'AR', 'INR', 'as.s', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(360, 'Indonesia', 'ID', 'IDN', 62, '', '001', NULL, 32400, 0, 'ID', 'IDR', 'as.se', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(364, 'Iran, Islamic Republic of', 'IR', 'IRN', 98, '0', '00', NULL, 12600, 0, 'AE', 'IRR', 'as.s', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(368, 'Iraq', 'IQ', 'IRQ', 964, '0', '00', NULL, 10800, 0, 'AR', 'IQD', 'as.w', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(372, 'Ireland', 'IE', 'IRL', 353, '', '00', NULL, 0, 0, 'EN', 'EUR', 'e.n', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(376, 'Israel', 'IL', 'ISR', 972, '', '00', NULL, 7200, 0, 'AR', 'ILS', 'as.w', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(380, 'Italy', 'IT', 'ITA', 39, '', '00', NULL, 3600, 0, 'CO', 'EUR', 'e.s', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(384, 'Ivory Coast', 'CI', 'CIV', 225, '0', '00', NULL, 0, 0, 'AK', 'XOF', 'af.w', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(388, 'Jamaica', 'JM', 'JAM', 1876, '1', '011', NULL, -18000, 0, 'EN', 'JMD', 'am.ca', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(392, 'Japan', 'JP', 'JPN', 81, '', '001', NULL, 32400, 0, 'JA', 'JPY', 'as.e', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(398, 'Kazakhstan', 'KZ', 'KAZ', 7, '8', '8~10', NULL, 21600, 0, 'AV', 'KZT', 'as.c', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(400, 'Jordan', 'JO', 'JOR', 962, '0', '00', NULL, 7200, 0, 'AR', 'JOD', 'as.w', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(404, 'Kenya', 'KE', 'KEN', 254, '', '000', NULL, 10800, 0, 'EN', 'KES', 'af.e', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(408, 'Korea, Democratic People Republic of', 'KP', 'PRK', 850, '0', '00', NULL, 32400, 0, 'KO', 'KPW', 'as.e', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(410, 'Korea, Republic of', 'KR', 'KOR', 82, '', '001', NULL, 32400, 0, 'KO', 'KRW', 'as.e', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(414, 'Kuwait', 'KW', 'KWT', 965, '0', '00', NULL, 10800, 0, 'AR', 'KWD', 'as.w', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(417, 'Kyrgyzstan', 'KG', 'KGZ', 996, '0', '00', NULL, 21600, 0, 'KY', 'KGS', 'as.c', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(418, 'Lao People Democratic Republic', 'LA', 'LAO', 856, '0', '14', NULL, 25200, 0, 'LO', 'LAK', 'as.se', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(422, 'Lebanon', 'LB', 'LBN', 961, '', '00', NULL, 7200, 0, 'AR', 'LBP', 'as.w', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(426, 'Lesotho', 'LS', 'LSO', 266, '0', '00', NULL, 7200, 0, 'EN', 'LSL', 'af.s', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(428, 'Latvia', 'LV', 'LVA', 371, '8', '00', NULL, 10800, 0, 'LV', 'LVL', 'e.n', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(430, 'Liberia', 'LR', 'LBR', 231, '22', '00', NULL, 0, 0, 'EN', 'LRD', 'af.w', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(434, 'Libyan Arab Jamahiriya', 'LY', 'LBY', 218, '0', '00', NULL, 7200, 0, 'AR', 'LYD', 'af.n', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(438, 'Liechtenstein', 'LI', 'LIE', 423, '', '00', NULL, 3600, 0, 'DE', 'CHF', 'e.w', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(440, 'Lithuania', 'LT', 'LTU', 370, '8', '00', NULL, 7200, 0, 'LT', 'LTL', 'e.n', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(442, 'Luxembourg', 'LU', 'LUX', 352, '', '00', NULL, 3600, 0, 'DE', 'EUR', 'e.w', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(446, 'Macao', 'MO', 'MAC', 853, '0', '00', NULL, 28800, 0, 'ZH', 'MOP', 'as.e', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(450, 'Madagascar', 'MG', 'MDG', 261, '0', '00', NULL, 10800, 0, 'FR', 'MGA', 'af.e', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(454, 'Malawi', 'MW', 'MWI', 265, '', '00', NULL, 7200, 0, 'EN', 'MWK', 'af.e', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(458, 'Malaysia', 'MY', 'MYS', 60, '0', '00', NULL, 28800, 0, 'JV', 'MYR', 'as.se', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(462, 'Maldives', 'MV', 'MDV', 960, '0', '00', NULL, 18000, 0, 'DV', 'MVR', 'as.s', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(466, 'Mali', 'ML', 'MLI', 223, '0', '00', NULL, 0, 0, 'BM', 'XOF', 'af.w', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(470, 'Malta', 'MT', 'MLT', 356, '0', '00', NULL, 3600, 0, 'EN', 'EUR', 'e.s', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(474, 'Martinique', 'MQ', 'MTQ', 596, '0', '00', NULL, -14400, 0, 'FR', 'EUR', 'am.ca', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(478, 'Mauritania', 'MR', 'MRT', 222, '0', '00', NULL, 0, 0, 'AR', 'MRO', 'af.w', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(480, 'Mauritius', 'MU', 'MUS', 230, '0', '00', NULL, 14400, 0, 'EN', 'MUR', 'af.e', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(484, 'Mexico', 'MX', 'MEX', 52, '01', '00', NULL, -21600, 0, 'ES', 'MXN', 'am.c', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(492, 'Monaco', 'MC', 'MCO', 377, '0', '00', NULL, 3600, 0, 'FR', 'EUR', 'e.w', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(496, 'Mongolia', 'MN', 'MNG', 976, '0', '001', NULL, 28800, 0, 'MN', 'MNT', 'as.e', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(498, 'Moldova, Republic of', 'MD', 'MDA', 373, '0', '00', NULL, 10800, 0, 'MO', 'MDL', 'e.e', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(499, 'Montenegro', 'ME', 'MNE', 382, NULL, NULL, NULL, 3600, 0, NULL, 'EUR', 'e.s', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(500, 'Montserrat', 'MS', 'MSR', 1664, NULL, NULL, NULL, -14400, 0, 'EN', 'XCD', 'am.ca', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(504, 'Morocco', 'MA', 'MAR', 212, '', '00', NULL, 0, 0, 'AR', 'MAD', 'af.n', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(508, 'Mozambique', 'MZ', 'MOZ', 258, '0', '00', NULL, 7200, 0, 'PT', 'MZN', 'af.e', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(512, 'Oman', 'OM', 'OMN', 968, '0', '00', NULL, 14400, 0, 'AR', 'OMR', 'as.w', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(516, 'Namibia', 'NA', 'NAM', 264, '0', '09', NULL, -14400, 0, 'EN', 'NAD', 'af.s', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(520, 'Nauru', 'NR', 'NRU', 674, '0', '00', NULL, 43200, 0, 'EN', 'AUD', 'o.mi', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(524, 'Nepal', 'NP', 'NPL', 977, '0', '00', NULL, 19800, 0, 'NE', 'NPR', 'as.s', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(528, 'Netherlands', 'NL', 'NLD', 31, '0', '00', NULL, 3600, 0, 'FY', 'EUR', 'e.w', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(530, 'Netherlands Antilles', 'AN', 'ANT', 599, '0', '00', NULL, -14400, 0, 'NL', 'ANG', 'am.ca', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(533, 'Aruba', 'AW', 'ABW', 297, '', '00', NULL, -14400, 0, 'NL', 'AWG', 'am.ca', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(540, 'New Caledonia', 'NC', 'NCL', 687, '0', '00', NULL, 39600, 0, 'FR', 'XPF', 'o.me', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(548, 'Vanuatu', 'VU', 'VUT', 678, '', '00', NULL, 39600, 0, 'BI', 'VUV', 'o.me', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(554, 'New Zealand', 'NZ', 'NZL', 64, '0', '00', NULL, 43200, 0, 'EN', 'NZD', 'o.a', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(558, 'Nicaragua', 'NI', 'NIC', 505, '0', '00', NULL, -21600, 0, 'ES', 'NIO', 'am.c', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(562, 'Niger', 'NE', 'NER', 227, '0', '00', NULL, 3600, 0, 'FF', 'XOF', 'af.w', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(566, 'Nigeria', 'NG', 'NGA', 234, '0', '009', NULL, 3600, 0, 'EN', 'NGN', 'af.w', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(570, 'Niue', 'NU', 'NIU', 683, '0', '00', NULL, -39600, 0, 'EN', 'NZD', 'o.p', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(574, 'Norfolk Island', 'NF', 'NFK', 672, '', '00', NULL, 41400, 0, 'EN', 'AUD', 'o.a', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(578, 'Norway', 'NO', 'NOR', 47, '', '00', NULL, 3600, 0, 'NB', 'NOK', 'e.n', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(580, 'Northern Mariana Islands', 'MP', 'MNP', 1670, '1', '011', NULL, 36000, 0, 'CH', 'USD', 'o.mi', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(581, 'United States Minor Outlying Islands', 'UM', 'UMI', NULL, '1', '011', NULL, -14400, 0, 'EN', 'USD', 'am.n', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(583, 'Micronesia, Federated States of', 'FM', 'FSM', 691, '1', '011', NULL, 36000, 0, 'EN', 'USD', 'o.mi', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(584, 'Marshall Islands', 'MH', 'MHL', 692, '1', '011', NULL, 36000, 0, 'EN', 'USD', 'o.mi', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(585, 'Palau', 'PW', 'PLW', 680, '', '011', NULL, 32400, 0, 'EN', 'USD', 'o.mi', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(586, 'Pakistan', 'PK', 'PAK', 92, '0', '00', NULL, 18000, 0, 'EN', 'PKR', 'as.s', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(591, 'Panama', 'PA', 'PAN', 507, '', '00', NULL, -18000, 0, 'ES', 'PAB', 'am.c', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(598, 'Papua New Guinea', 'PG', 'PNG', 675, '', '05', NULL, 36000, 0, 'EN', 'PGK', 'o.me', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(600, 'Paraguay', 'PY', 'PRY', 595, '0', '002', NULL, -14400, 0, 'ES', 'PYG', 'am.s', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(604, 'Peru', 'PE', 'PER', 51, '0', '00', NULL, -18000, 0, 'AY', 'PEN', 'am.s', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(608, 'Philippines', 'PH', 'PHL', 63, '0', '00', NULL, 28800, 0, 'EN', 'PHP', 'as.se', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(612, 'Pitcairn', 'PN', 'PCN', NULL, NULL, NULL, NULL, NULL, 0, 'EN', NULL, NULL, 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(616, 'Poland', 'PL', 'POL', 48, '0', '00', NULL, 3600, 0, 'DE', 'PLN', 'e.e', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(620, 'Portugal', 'PT', 'PRT', 351, '', '00', NULL, 3600, 0, 'PT', 'EUR', 'e.s', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(624, 'Guinea-Bissau', 'GW', 'GNB', 245, '', '00', NULL, -3600, 0, 'PT', 'XOF', 'af.w', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(626, 'Timor-Leste', 'TL', 'TLS', 670, '', '00', NULL, 36000, 0, 'PT', 'USD', 'as.se', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(630, 'Puerto Rico', 'PR', 'PRI', 1787, NULL, NULL, NULL, -14400, 0, 'EN', 'USD', 'am.ca', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(634, 'Qatar', 'QA', 'QAT', 974, '0', '0', NULL, 10800, 0, 'AR', 'QAR', 'as.w', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(638, 'Reunion', 'RE', 'REU', 262, '0', '00', NULL, 14400, 0, 'FR', 'EUR', 'af.e', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(642, 'Romania', 'RO', 'ROU', 40, '0', '00', NULL, 7200, 0, 'CU', 'RON', 'e.e', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(643, 'Russian Federation', 'RU', 'RUS', 7, '8', '8~10', NULL, 21600, 0, 'AV', 'RUB', 'e.e', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(646, 'Rwanda', 'RW', 'RWA', 250, '0', '00', NULL, 7200, 0, 'EN', 'RWF', 'af.e', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(652, 'Saint Barthelemy', 'BL', 'BLM', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(654, 'Saint Helena, Ascension and Tristan da Cunha', 'SH', 'SHN', 290, '', '00', NULL, 0, 0, 'EN', 'SHP', 'af.w', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(659, 'Saint Kitts and Nevis', 'KN', 'KNA', 1869, '1', '011', NULL, -14400, 0, 'EN', 'XCD', 'am.ca', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(660, 'Anguilla', 'AI', 'AIA', 1264, '1', '011', NULL, -14400, 0, 'EN', 'XCD', 'am.ca', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(662, 'Saint Lucia', 'LC', 'LCA', 1758, '1', '011', NULL, -14400, 0, 'EN', 'XCD', 'am.ca', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(663, 'Saint Martin (French part)', 'MF', 'MAF', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(666, 'Saint Pierre and Miquelon', 'PM', 'SPM', 508, '0', '00', NULL, -10800, 0, 'FR', 'EUR', 'am.n', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(670, 'Saint Vincent and the Grenadines', 'VC', 'VCT', 1784, '1', '011', NULL, -14400, 0, 'EN', 'XCD', 'am.ca', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(674, 'San Marino', 'SM', 'SMR', 378, '0', '00', NULL, 3600, 0, 'IT', 'EUR', 'e.s', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(678, 'Sao Tome and Principe', 'ST', 'STP', 239, '0', '00', NULL, 0, 0, 'PT', 'STD', 'af.m', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(682, 'Saudi Arabia', 'SA', 'SAU', 966, '0', '00', NULL, 10800, 0, 'AR', 'SAR', 'as.w', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(686, 'Senegal', 'SN', 'SEN', 221, '0', '00', NULL, 0, 0, 'FF', 'XOF', 'af.w', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(688, 'Serbia', 'RS', 'SRB', 381, '0', '99', NULL, 3600, 0, NULL, 'RSD', 'e.s', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(690, 'Seychelles', 'SC', 'SYC', 248, '0', '00', NULL, 14400, 0, 'EN', 'SCR', 'af.e', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(694, 'Sierra Leone', 'SL', 'SLE', 232, '0', '00', NULL, 0, 0, 'EN', 'SLL', 'af.w', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(702, 'Singapore', 'SG', 'SGP', 65, '', '001', NULL, 28800, 0, 'BN', 'SGD', 'as.se', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(703, 'Slovakia', 'SK', 'SVK', 421, '0', '00', NULL, 3600, 0, 'HU', 'EUR', 'e.e', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(704, 'Viet Nam', 'VN', 'VNM', 84, '0', '00', NULL, 25200, 0, 'VI', 'VND', 'as.se', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(705, 'Slovenia', 'SI', 'SVN', 386, '0', '00', NULL, 3600, 0, 'HU', 'EUR', 'e.s', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(706, 'Somalia', 'SO', 'SOM', 252, '', '00', NULL, 10800, 0, 'AR', 'SLS', 'af.e', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(710, 'South Africa', 'ZA', 'ZAF', 27, '0', '09', NULL, 7200, 0, 'AF', 'ZAR', 'af.s', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(716, 'Zimbabwe', 'ZW', 'ZWE', 263, '0', '110', NULL, 7200, 0, 'EN', 'ZWL', 'af.e', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(724, 'Spain', 'ES', 'ESP', 34, '', '00', NULL, 3600, 0, 'AN', 'EUR', 'e.s', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(732, 'Western Sahara', 'EH', 'ESH', 212, '', '00', NULL, 0, 0, NULL, 'MAD', 'af.n', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(736, 'Sudan', 'SD', 'SDN', 249, '0', '00', NULL, 7200, 0, 'AR', 'SDG', 'af.n', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(740, 'Suriname', 'SR', 'SUR', 597, '', '00', NULL, -12600, 0, 'JV', 'SRD', 'am.s', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(744, 'Svalbard and Jan Mayen', 'SJ', 'SJM', 47, '', '00', NULL, 3600, 0, NULL, 'NOK', 'e.n', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(748, 'Swaziland', 'SZ', 'SWZ', 268, '', '00', NULL, -14400, 0, 'EN', 'SZL', 'af.s', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(752, 'Sweden', 'SE', 'SWE', 46, '0', '00', NULL, 3600, 0, 'FI', 'SEK', 'e.n', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(756, 'Switzerland', 'CH', 'CHE', 41, '0', '00', NULL, 3600, 0, 'DE', 'CHF', 'e.w', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(760, 'Syrian Arab Republic', 'SY', 'SYR', 963, '', '00', NULL, 7200, 0, 'AR', 'SYP', 'as.w', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(762, 'Tajikistan', 'TJ', 'TJK', 992, '8', '8~10', NULL, 21600, 0, 'OS', 'TJS', 'as.c', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(764, 'Thailand', 'TH', 'THA', 66, '', '001', NULL, 25200, 0, 'SI', 'THB', 'as.se', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(768, 'Togo', 'TG', 'TGO', 228, '', '00', NULL, 0, 0, 'EE', 'XOF', 'af.w', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(772, 'Tokelau', 'TK', 'TKL', 690, '', '00', NULL, -36000, 0, 'EN', 'NZD', 'o.p', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(776, 'Tonga', 'TO', 'TON', 676, '', '00', NULL, 46800, 0, 'EN', 'TOP', 'o.p', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(780, 'Trinidad and Tobago', 'TT', 'TTO', 1868, '1', '011', NULL, -14400, 0, 'EN', 'TTD', 'am.ca', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(784, 'United Arab Emirates', 'AE', 'ARE', 971, '', '00', NULL, 14400, 0, 'AR', 'AED', 'as.w', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(788, 'Tunisia', 'TN', 'TUN', 216, '0', '00', NULL, 3600, 0, 'AR', 'TND', 'af.n', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(792, 'Turkey', 'TR', 'TUR', 90, '0', '00', NULL, 7200, 0, 'AB', 'TRY', 'as.w', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(795, 'Turkmenistan', 'TM', 'TKM', 993, '8', '8~10', NULL, 18000, 0, 'OS', 'TMT', 'as.c', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(796, 'Turks and Caicos Islands', 'TC', 'TCA', 1649, '1', '011', NULL, -18000, 0, 'EN', 'USD', 'am.ca', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(798, 'Tuvalu', 'TV', 'TUV', 688, '', '00', NULL, 43200, 0, 'GI', 'AUD', 'o.p', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(800, 'Uganda', 'UG', 'UGA', 256, '', '000', NULL, 10800, 0, 'EN', 'UGX', 'af.e', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(804, 'Ukraine', 'UA', 'UKR', 380, '8', '8~10', NULL, 10800, 0, 'AB', 'UAH', 'e.e', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(807, 'Macedonia, the former Yugoslav Republic of', 'MK', 'MKD', 389, '0', '00', NULL, 3600, 0, 'CU', 'MKD', 'e.s', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(818, 'Egypt', 'EG', 'EGY', 20, '0', '00', NULL, 7200, 0, 'AR', 'EGP', 'af.n', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(826, 'United Kingdom', 'GB', 'GBR', 44, '0', '00', NULL, 0, 0, 'CY', 'GBP', 'e.n', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(831, 'Guernsey', 'GG', 'GGY', 44, '0', '00', NULL, 0, 0, NULL, 'GGP', 'e.n', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(832, 'Jersey', 'JE', 'JEY', 44, '0', '00', NULL, 0, 0, NULL, 'JEP', 'e.n', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(833, 'Isle of Man', 'IM', 'IMN', 44, '0', '00', NULL, 0, 0, NULL, 'IMP', 'e.n', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(834, 'Tanzania, United Republic of', 'TZ', 'TZA', 255, '0', '000', NULL, 10800, 0, 'SW', 'TZS', 'af.e', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(840, 'United States', 'US', 'USA', 1, '1', '011', NULL, -14400, 0, 'EN', 'USD', 'am.n', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(850, 'Virgin Islands, U.S.', 'VI', 'VIR', 1340, '1', '011', NULL, -14400, 0, 'EN', 'USD', 'am.ca', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(854, 'Burkina Faso', 'BF', 'BFA', 226, '', '00', NULL, 0, 0, 'BM', 'XOF', 'af.w', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(858, 'Uruguay', 'UY', 'URY', 598, '0', '00', NULL, -10800, 0, 'ES', 'UYU', 'am.s', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(860, 'Uzbekistan', 'UZ', 'UZB', 998, '8', '8~10', NULL, 21600, 0, 'OS', 'UZS', 'as.c', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(862, 'Venezuela, Bolivarian Republic of', 'VE', 'VEN', 58, '', '00', NULL, -14400, 0, 'ES', 'VEF', 'am.s', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(876, 'Wallis and Futuna', 'WF', 'WLF', 681, '', '19', NULL, 43200, 0, 'FR', 'XPF', 'o.p', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(882, 'Samoa', 'WS', 'WSM', 685, '', '0', NULL, -39600, 0, 'EN', 'WST', 'o.p', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(887, 'Yemen', 'YE', 'YEM', 967, '0', '00', NULL, 10800, 0, 'AR', 'YER', 'as.w', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(894, 'Zambia', 'ZM', 'ZMB', 260, '0', '00', NULL, 7200, 0, 'EN', 'ZMK', 'af.e', 1289015931, 1, NULL, NULL);
-- following is list of countries with dialing code it will be used to identify a dialing code as invalid when it failed to match from above
INSERT INTO country VALUES(1000, 'Unknown 28',  'UU', 'UUU', 28 , '0', '00', NULL, 0, 0, 'EN', 'USD', 'u', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(1001, 'Unknown 80',  'UU', 'UUU', 80 , '0', '00', NULL, 0, 0, 'EN', 'USD', 'u', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(1002, 'Unknown 83',  'UU', 'UUU', 83 , '0', '00', NULL, 0, 0, 'EN', 'USD', 'u', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(1003, 'Unknown 87',  'UU', 'UUU', 87 , '0', '00', NULL, 0, 0, 'EN', 'USD', 'u', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(1004, 'Unknown 89',  'UU', 'UUU', 89 , '0', '00', NULL, 0, 0, 'EN', 'USD', 'u', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(1005, 'Unknown 259', 'UU', 'UUU', 259, '0', '00', NULL, 0, 0, 'EN', 'USD', 'u', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(1006, 'Unknown 292', 'UU', 'UUU', 292, '0', '00', NULL, 0, 0, 'EN', 'USD', 'u', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(1007, 'Unknown 293', 'UU', 'UUU', 293, '0', '00', NULL, 0, 0, 'EN', 'USD', 'u', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(1008, 'Unknown 294', 'UU', 'UUU', 294, '0', '00', NULL, 0, 0, 'EN', 'USD', 'u', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(1009, 'Unknown 295', 'UU', 'UUU', 295, '0', '00', NULL, 0, 0, 'EN', 'USD', 'u', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(1010, 'Unknown 296', 'UU', 'UUU', 296, '0', '00', NULL, 0, 0, 'EN', 'USD', 'u', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(1011, 'Unknown 357', 'UU', 'UUU', 357, '0', '00', NULL, 0, 0, 'EN', 'USD', 'u', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(1012, 'Unknown 671', 'UU', 'UUU', 671, '0', '00', NULL, 0, 0, 'EN', 'USD', 'u', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(1013, 'Unknown 684', 'UU', 'UUU', 684, '0', '00', NULL, 0, 0, 'EN', 'USD', 'u', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(1014, 'Unknown 693', 'UU', 'UUU', 693, '0', '00', NULL, 0, 0, 'EN', 'USD', 'u', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(1015, 'Unknown 694', 'UU', 'UUU', 694, '0', '00', NULL, 0, 0, 'EN', 'USD', 'u', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(1016, 'Unknown 695', 'UU', 'UUU', 695, '0', '00', NULL, 0, 0, 'EN', 'USD', 'u', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(1017, 'Unknown 696', 'UU', 'UUU', 696, '0', '00', NULL, 0, 0, 'EN', 'USD', 'u', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(1018, 'Unknown 697', 'UU', 'UUU', 697, '0', '00', NULL, 0, 0, 'EN', 'USD', 'u', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(1019, 'Unknown 698', 'UU', 'UUU', 698, '0', '00', NULL, 0, 0, 'EN', 'USD', 'u', 1289015931, 1, NULL, NULL);
INSERT INTO country VALUES(1020, 'Unknown 699', 'UU', 'UUU', 699, '0', '00', NULL, 0, 0, 'EN', 'USD', 'u', 1289015931, 1, NULL, NULL);

/*==============================================================*/
/* Table: service                                               */
/* Desc: List of system wide available services                 */
/*==============================================================*/
CREATE TABLE service
(
   service_flag                  int(11)                NOT NULL,
   name                          varchar(64)            NOT NULL,
   unit_id                       varchar(16)            NOT NULL,
   PRIMARY KEY (service_flag)
) ENGINE = InnoDB;

/*==============================================================*/
/* Table: dialplan                                              */
/*==============================================================*/
CREATE TABLE dialplan
(
   dialplan_id                   int(11) unsigned       NOT NULL auto_increment,
   gateway_flag                  int(11)                default 15,
   source                        varchar(128)           default '*',
   destination                   varchar(128)           default '*',
   context                       varchar(16)            default '*',
   weight                        int(11)                default 0,
   program_id                    varchar(128)           default NULL,
   application_id                varchar(128)           default NULL,
   filter_flag                   int(11)                default 31,
   PRIMARY KEY  (dialplan_id),
   UNIQUE KEY dialplan_check (gateway_flag, source, destination, context)
) ENGINE = InnoDB;
CREATE INDEX dialplan_source ON dialplan (source);
CREATE INDEX dialplan_destination ON dialplan (destination);
CREATE INDEX dialplan_context ON dialplan (context);

/*==============================================================*/
/* Desc: Dumping basic services into service table              */
/*==============================================================*/
-- <?php t('voice'); ?>
INSERT INTO service VALUES (1, 'voice', 1);
-- <?php t('fax'); ?>
INSERT INTO service VALUES (2, 'fax', 1);
-- <?php t('sms'); ?>
INSERT INTO service VALUES (4, 'sms', 3);
-- <?php t('email'); ?>
INSERT INTO service VALUES (8, 'email', 4);
-- <?php t('video'); ?>
INSERT INTO service VALUES (16, 'video', 1);


/*==============================================================*/
/* Table: unit                                                  */
/* Desc: List of units as per service type                      */
/*==============================================================*/
CREATE TABLE unit
(
   unit_id                       int(11)                NOT NULL auto_increment,
   name                          varchar(64)            NOT NULL,
   measurement                   varchar(16)            NOT NULL,
   PRIMARY KEY (unit_id)
) ENGINE = InnoDB;

/*==============================================================*/
/* Desc: Dumping basic units into unit table                    */
/*==============================================================*/
-- <?php t('second'); t('time'); ?>
INSERT INTO unit VALUES (1, 'second', 'time');
-- <?php t('page'); t('count'); ?>
INSERT INTO unit VALUES (2, 'page', 'count');
-- <?php t('segment'); t('count'); ?>
INSERT INTO unit VALUES (3, 'segment', 'count');
-- <?php t('page'); t('count'); ?>
INSERT INTO unit VALUES (4, 'page', 'count');


/*==============================================================*/
/* Table: unit_block                                            */
/* Desc: allowed block for each unit                            */
/*==============================================================*/
CREATE TABLE unit_block
(
   unit_id                       int(11)                NOT NULL,
   block                         int(11)                NOT NULL
) ENGINE = InnoDB;

/*==============================================================*/
/* Desc: Dumping basic unit block into unit_block table         */
/*==============================================================*/
INSERT INTO unit_block VALUES (1, 1);
INSERT INTO unit_block VALUES (1, 5);
INSERT INTO unit_block VALUES (1, 6);
INSERT INTO unit_block VALUES (1, 10);
INSERT INTO unit_block VALUES (1, 12);
INSERT INTO unit_block VALUES (1, 15);
INSERT INTO unit_block VALUES (1, 18);
INSERT INTO unit_block VALUES (1, 20);
INSERT INTO unit_block VALUES (1, 24);
INSERT INTO unit_block VALUES (1, 25);
INSERT INTO unit_block VALUES (1, 30);
INSERT INTO unit_block VALUES (1, 35);
INSERT INTO unit_block VALUES (1, 36);
INSERT INTO unit_block VALUES (1, 40);
INSERT INTO unit_block VALUES (1, 42);
INSERT INTO unit_block VALUES (1, 45);
INSERT INTO unit_block VALUES (1, 48);
INSERT INTO unit_block VALUES (1, 50);
INSERT INTO unit_block VALUES (1, 54);
INSERT INTO unit_block VALUES (1, 60);
INSERT INTO unit_block VALUES (2, 1);
INSERT INTO unit_block VALUES (3, 1);
INSERT INTO unit_block VALUES (4, 1);


/*==============================================================*/
/* Table: carriertype                                           */
/* Desc: various communication carrier type like gsm            */
/*==============================================================*/
CREATE TABLE carriertype
(
   carriertype_id                int(11)                NOT NULL,
   name                          varchar(64)            NOT NULL,
   service_flag                  int(11)                default NULL,
   PRIMARY KEY (carriertype_id)
) ENGINE = InnoDB;

/*==============================================================*/
/* Desc: Dumping basic carriertypes into carriertype table      */
/*==============================================================*/
INSERT INTO carriertype VALUES (1, 'landline', 3); -- fax and voice
INSERT INTO carriertype VALUES (2, 'mobile', 5); -- sms and voice
INSERT INTO carriertype VALUES (3, 'email', 8); -- email


/*==============================================================*/
/* Table: sequence                                              */
/* Desc: store index sequences of all tables                    */
/*==============================================================*/
CREATE TABLE sequence
(
   sequence_id                   int(11) unsigned       NOT NULL auto_increment,
   table_name                    varchar(128)           NOT NULL,
   sequence                      int(11) unsigned       NOT NULL,
   PRIMARY KEY (sequence_id),
   KEY (table_name)
) ENGINE = InnoDB;
CREATE INDEX sequence_table_name ON sequence (table_name(3));

DELIMITER |
CREATE FUNCTION next_record_id(itemName VARCHAR(128))
  RETURNS INT(11)
  BEGIN
    DECLARE newRecordID, sequenceID INT(11);

    IF EXISTS(SELECT sequence_id FROM sequence WHERE table_name=itemName limit 1) THEN
      UPDATE sequence SET sequence=sequence+1 WHERE table_name=itemName;
    ELSE
      INSERT INTO sequence (table_name, sequence) VALUES (itemName, 1);
    END IF;

    SELECT sequence INTO newRecordID FROM sequence WHERE table_name=itemName;
    RETURN newRecordID;
  END;
|
DELIMITER ;

/*==============================================================*/
/* Table: auto_number                                           */
/* Desc: for automaticail number generation                     */
/*==============================================================*/
CREATE TABLE auto_number
(
   num0                          int(11) unsigned       NOT NULL,
   num1                          int(11) unsigned       NOT NULL,
   num2                          int(11) unsigned       NOT NULL,
   num3                          int(11) unsigned       NOT NULL,
   num4                          int(11) unsigned       NOT NULL
) ENGINE = InnoDB;

/*==============================================================*/
/* Desc: Dumping data in auto_number table table                */
/*==============================================================*/
INSERT INTO auto_number VALUES (0, 1, 2, 3, 4);
INSERT INTO auto_number SELECT num0+1, num1+1, num2+1, num3+1, num4+1 FROM auto_number;
INSERT INTO auto_number SELECT num0+2, num1+2, num2+2, num3+2, num4+2 FROM auto_number;
INSERT INTO auto_number SELECT num0+4, num1+4, num2+4, num3+4, num4+4 FROM auto_number;
INSERT INTO auto_number SELECT num0+8, num1+8, num2+8, num3+8, num4+8 FROM auto_number;
INSERT INTO auto_number SELECT num0+16, num1+16, num2+16, num3+16, num4+16 FROM auto_number;
INSERT INTO auto_number SELECT num0+32, num1+32, num2+32, num3+32, num4+32 FROM auto_number;
INSERT INTO auto_number SELECT num0+64, num1+64, num2+64, num3+64, num4+64 FROM auto_number;
INSERT INTO auto_number SELECT num0+128, num1+128, num2+128, num3+128, num4+128 FROM auto_number;
