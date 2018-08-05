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
