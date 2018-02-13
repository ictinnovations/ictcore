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
INSERT INTO permission VALUES (NULL, 'statistic_read', '');
INSERT INTO permission VALUES (NULL, 'configuration_read', '');
INSERT INTO permission VALUES (NULL, 'configuration_write', '');
-- Program permissions
INSERT INTO permission VALUES (NULL, 'program', '');
INSERT INTO permission VALUES (NULL, 'program_create', '');
INSERT INTO permission VALUES (NULL, 'program_list', '');
INSERT INTO permission VALUES (NULL, 'program_read', '');
INSERT INTO permission VALUES (NULL, 'program_delete', '');
INSERT INTO permission VALUES (NULL, 'program_execute', '');
-- Transmission permissions
INSERT INTO permission VALUES (NULL, 'transmission', '');
INSERT INTO permission VALUES (NULL, 'transmission_create', '');
INSERT INTO permission VALUES (NULL, 'transmission_list', '');
INSERT INTO permission VALUES (NULL, 'transmission_read', '');
INSERT INTO permission VALUES (NULL, 'transmission_update', '');
INSERT INTO permission VALUES (NULL, 'transmission_delete', '');
INSERT INTO permission VALUES (NULL, 'transmission_send', '');
-- Task permissions
INSERT INTO permission VALUES (NULL, 'task', '');
INSERT INTO permission VALUES (NULL, 'task_create', '');
INSERT INTO permission VALUES (NULL, 'task_read', '');
INSERT INTO permission VALUES (NULL, 'task_list', '');
INSERT INTO permission VALUES (NULL, 'task_delete', '');
-- Schedule permissions
INSERT INTO permission VALUES (NULL, 'schedule', '');
INSERT INTO permission VALUES (NULL, 'schedule_create', '');
INSERT INTO permission VALUES (NULL, 'schedule_read', '');
INSERT INTO permission VALUES (NULL, 'schedule_list', '');
INSERT INTO permission VALUES (NULL, 'schedule_delete', '');
-- Spool permissions
INSERT INTO permission VALUES (NULL, 'spool', '');
INSERT INTO permission VALUES (NULL, 'spool_read', '');
INSERT INTO permission VALUES (NULL, 'spool_list', '');
-- Result permissions
INSERT INTO permission VALUES (NULL, 'result', '');
INSERT INTO permission VALUES (NULL, 'result_read', '');
INSERT INTO permission VALUES (NULL, 'result_list', '');
-- Provider permissions
INSERT INTO permission VALUES (NULL, 'provider', '');
INSERT INTO permission VALUES (NULL, 'provider_create', '');
INSERT INTO permission VALUES (NULL, 'provider_list', '');
INSERT INTO permission VALUES (NULL, 'provider_read', '');
INSERT INTO permission VALUES (NULL, 'provider_update', '');
INSERT INTO permission VALUES (NULL, 'provider_delete', '');
-- Contact permissions
INSERT INTO permission VALUES (NULL, 'contact', '');
INSERT INTO permission VALUES (NULL, 'contact_create', '');
INSERT INTO permission VALUES (NULL, 'contact_list', '');
INSERT INTO permission VALUES (NULL, 'contact_read', '');
INSERT INTO permission VALUES (NULL, 'contact_update', '');
INSERT INTO permission VALUES (NULL, 'contact_delete', '');
-- GROUP permissions
INSERT INTO permission VALUES (NULL, 'group', '');
INSERT INTO permission VALUES (NULL, 'group_create', '');
INSERT INTO permission VALUES (NULL, 'group_list', '');
INSERT INTO permission VALUES (NULL, 'group_read', '');
INSERT INTO permission VALUES (NULL, 'group_update', '');
INSERT INTO permission VALUES (NULL, 'group_delete', '');
-- Account permissions
INSERT INTO permission VALUES (NULL, 'account', '');
INSERT INTO permission VALUES (NULL, 'account_create', '');
INSERT INTO permission VALUES (NULL, 'account_list', '');
INSERT INTO permission VALUES (NULL, 'account_read', '');
INSERT INTO permission VALUES (NULL, 'account_update', '');
INSERT INTO permission VALUES (NULL, 'account_delete', '');
-- User permissions
INSERT INTO permission VALUES (NULL, 'user', '');
INSERT INTO permission VALUES (NULL, 'user_create', '');
INSERT INTO permission VALUES (NULL, 'user_list', '');
INSERT INTO permission VALUES (NULL, 'user_read', '');
INSERT INTO permission VALUES (NULL, 'user_update', '');
INSERT INTO permission VALUES (NULL, 'user_delete', '');
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
   delay                     varchar(128)           NOT NULL default '',
   try_allowed               varchar(128)           NOT NULL default '',
   contact_total             int(11)                NOT NULL default 0,
   contact_done              int(11)                NOT NULL default 0,
   status                    varchar(128)           NOT NULL default '',
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
CREATE INDEX program_resource_resource_type ON program_resource (resource_type);

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
