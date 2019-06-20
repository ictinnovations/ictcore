INSERT INTO permission VALUES (NULL, 'permission_delete', '');

INSERT INTO configuration VALUES (NULL,'service','voice_status','0',254); --ready
INSERT INTO configuration VALUES (NULL,'service','fax_status','0',254); --ready
INSERT INTO configuration VALUES (NULL,'service','sms_status','0',254); --ready
INSERT INTO configuration VALUES (NULL,'service','email_status','0',254); --ready


ALTER TABLE transmission ADD campaign_id                 int(11)                  default NULL;


ALTER TABLE campaign CHANGE account_id account_id                int(11)                default NULL;
ALTER TABLE campaign ADD contact_total             int(11)                NOT NULL default 0;
ALTER TABLE campaign ADD contact_done              int(11)                NOT NULL default 0;
ALTER TABLE campaign ADD date_created              int(11)                default NULL;
ALTER TABLE campaign ADD last_updated              int(11)                default NULL;
ALTER TABLE campaign ADD updated_by              int(11) unsigned       default NULL;

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


ALTER TABLE contact_group CHANGE contact_total contact_total                 int(11)                NOT NULL default 0;
ALTER TABLE contact_group ADD date_created                  int(11)                default NULL;
ALTER TABLE contact_group ADD created_by                    int(11)                default NULL;
ALTER TABLE contact_group ADD last_updated                  int(11)                default NULL;
ALTER TABLE contact_group ADD updated_by                    int(11) unsigned       default NULL;

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

ALTER TABLE account ADD CONSTRAINT username UNIQUE (username, type);
ALTER TABLE usr ADD CONSTRAINT username UNIQUE (username);
ALTER TABLE usr ADD CONSTRAINT email UNIQUE (email);


INSERT INTO permission VALUES (NULL, 'statistic_read', ''); 
INSERT INTO permission VALUES (NULL, 'configuration_read', ''); 
INSERT INTO permission VALUES (NULL, 'configuration_write', ''); 


SELECT @roleId := role_id FROM role WHERE name='admin';
SELECT @permissionId := permission_id FROM permission WHERE name='statistic'; 
INSERT INTO role_permission VALUES (NULL, @roleId, @permissionId);   /* statistic */ 
SELECT @permissionId := permission_id FROM permission WHERE name='configuration'; 
INSERT INTO role_permission VALUES (NULL, @roleId, @permissionId);   /* configuration */

SELECT @roleId := role_id FROM role WHERE name='user';
SELECT @permissionId := permission_id FROM permission WHERE name='statistic_read'; 
INSERT INTO role_permission VALUES (NULL, @roleId, @permissionId);   /* statistic_read */ 
SELECT @permissionId := permission_id FROM permission WHERE name='configuration_read'; 
INSERT INTO role_permission VALUES (NULL, @roleId, @permissionId);   /* configuration_read */

-- version 0.8.0.8
ALTER TABLE campaign CHANGE delay cpm                       int(11)                NOT NULL default 2;
ALTER TABLE campaign CHANGE try_allowed try_allowed               int(11)                NOT NULL default 1;

-- version 0.8.1.0
ALTER TABLE temeplate CHANGE attachment attachment               text;

-- version 0.8.4.0
INSERT INTO permission VALUES (NULL, 'usr', '');
INSERT INTO permission VALUES (NULL, 'usr_create', '');
INSERT INTO permission VALUES (NULL, 'usr_list', '');
INSERT INTO permission VALUES (NULL, 'usr_read', '');
INSERT INTO permission VALUES (NULL, 'usr_update', '');
INSERT INTO permission VALUES (NULL, 'usr_delete', '');

SELECT @roleId := role_id FROM role WHERE name='admin';
SELECT @permissionId := permission_id FROM permission WHERE name='usr';
INSERT INTO role_permission VALUES (NULL, @roleId, @permissionId);   /* usr */

SELECT @roleId := role_id FROM role WHERE name='user';
SELECT @permissionId := permission_id FROM permission WHERE name='user_read';
INSERT INTO role_permission VALUES (NULL, @roleId, @permissionId);   /* user_read */
SELECT @permissionId := permission_id FROM permission WHERE name='usr_read';
INSERT INTO role_permission VALUES (NULL, @roleId, @permissionId);   /* usr_read */
SELECT @permissionId := permission_id FROM permission WHERE name='account_read';
INSERT INTO role_permission VALUES (NULL, @roleId, @permissionId);   /* account_read */
SELECT @permissionId := permission_id FROM permission WHERE name='account_update';
INSERT INTO role_permission VALUES (NULL, @roleId, @permissionId);   /* account_update */
SELECT @permissionId := permission_id FROM permission WHERE name='account_list';
INSERT INTO role_permission VALUES (NULL, @roleId, @permissionId);   /* account_list */


alter table program add column updated_by                      int(11) unsigned       default NULL after parent_id;
alter table program add column last_updated                    int(11)                default NULL after parent_id;
alter table program add column created_by                      int(11)                default NULL after parent_id;
alter table program add column date_created                    int(11)                default NULL after parent_id;

SET foreign_key_checks = 0;
ALTER DATABASE ictfax CHARACTER SET utf8 COLLATE utf8_general_ci;
-- mysql --database=ictfax -B -N -e "SHOW TABLES" | awk '{print "SET foreign_key_checks = 0; ALTER TABLE", $1, "CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci; SET foreign_key_checks = 1; "}' | mysql --database=ictfax
ALTER TABLE account            CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE action             CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE application        CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE auto_number        CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE campaign           CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE carriertype        CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE codec              CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE config             CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE config_data        CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE config_node        CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE configuration      CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE configuration_data CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE contact            CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE contact_group      CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE contact_link       CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE csv_users          CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE dialplan           CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE document           CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE gateway            CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE ivr                CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE node               CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE permission         CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE program            CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE program_resource   CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE provider           CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE resource           CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE role               CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE role_permission    CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE role_resource      CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE schedule           CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE sequence           CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE service            CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE session            CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE spool              CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE spool_result       CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE task               CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE template           CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE transmission       CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE unit               CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE unit_block         CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE user_permission    CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE user_resource      CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE user_role          CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE usr                CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
SET foreign_key_checks = 1;

INSERT INTO permission VALUES (NULL, 'user_password', '');
INSERT INTO permission VALUES (NULL, 'usr_password', '');
SELECT @permissionId := permission_id FROM permission WHERE name='user_password';
INSERT INTO role_permission VALUES (NULL, @roleId, @permissionId);   /* user_password */
SELECT @roleId := role_id FROM role WHERE name='user';
INSERT INTO role_permission VALUES (NULL, @roleId, @permissionId);   /* user_password */
SELECT @permissionId := permission_id FROM permission WHERE name='usr_password';
INSERT INTO role_permission VALUES (NULL, @roleId, @permissionId);   /* usr_password */
