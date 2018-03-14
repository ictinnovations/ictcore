ALTER TABLE task MODIFY data data                          text;

SELECT @usrId := usr_id FROM usr WHERE username='admin';
SELECT @roleId := role_id FROM role WHERE name='user';
INSERT INTO user_role VALUES (@roleId, @usrId);

-- Common permissions
INSERT INTO permission VALUES (NULL, 'api', '');
INSERT INTO permission VALUES (NULL, 'api_access', '');

-- permissions for admin role
SELECT @roleId := role_id FROM role WHERE name='admin';
-- provider permissions
SELECT @permissionId := permission_id FROM permission WHERE name='api';
INSERT INTO role_permission VALUES (NULL, @roleId, @permissionId);   /* api */

-- permissions for user role
SELECT @roleId := role_id FROM role WHERE name='user';
-- provider permissions
SELECT @permissionId := permission_id FROM permission WHERE name='api';
INSERT INTO role_permission VALUES (NULL, @roleId, @permissionId);   /* api */
SELECT @permissionId := permission_id FROM permission WHERE name='task';
INSERT INTO role_permission VALUES (NULL, @roleId, @permissionId);   /* task */

DROP TABLE transmission_session;
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
/* Table: campaign                                              */
/* Desc: user can  create campaign*/
/*==============================================================*/
CREATE TABLE campaign
(
   campaign_id               int(11) unsigned       NOT NULL auto_increment,
   program_id                int(11)                NOT NULL,
   group_id                  int(11)                NOT NULL ,
   delay                     varchar(128)           NOT NULL default '',
   try_allowed               varchar(128)           NOT NULL default '',
   account_id                int(11)                NOT NULL ,
   status                    varchar(128)           NOT NULL default '',
   created_by                int(11)                default NULL,
   pid                       varchar(128)           NOT NULL default '',
   last_run                   int(11)                default NULL,
   PRIMARY KEY (campaign_id)
) ENGINE = InnoDB;
CREATE INDEX campaign_created_by ON campaign (created_by);

/*==============================================================*/
/* Table: group                                                 */
/* Desc: user can create group                                  */
/*==============================================================*/
CREATE TABLE contact_group
(
   group_id                      int(11) unsigned       NOT NULL auto_increment,
   name                          varchar(128)           NOT NULL,
   description                   varchar(255)           NOT NULL default '',
   contact_count                 varchar(128)           NOT NULL default '0',
   PRIMARY KEY (group_id)
) ENGINE = InnoDB;

/*==============================================================*/
/* Table: group_contacts                                        */
/* Desc: link table for contact and group                       */
/*==============================================================*/
CREATE TABLE contact_link
(
   group_id                      int(11)                NOT NULL,
   contact_id                    int(11)                NOT NULLS,
   PRIMARY KEY (group_id, contact_id)
) ENGINE = InnoDB;

-- GROUP permissions
INSERT INTO permission VALUES (NULL, 'group', '');
INSERT INTO permission VALUES (NULL, 'group_create', '');
INSERT INTO permission VALUES (NULL, 'group_list', '');
INSERT INTO permission VALUES (NULL, 'group_read', '');
INSERT INTO permission VALUES (NULL, 'group_update', '');
INSERT INTO permission VALUES (NULL, 'group_delete', '');
-- Campaign permissions
INSERT INTO permission VALUES (NULL, 'campaign', '');
INSERT INTO permission VALUES (NULL, 'campaign_create', '');
INSERT INTO permission VALUES (NULL, 'campaign_list', '');
INSERT INTO permission VALUES (NULL, 'campaign_read', '');
INSERT INTO permission VALUES (NULL, 'campaign_update', '');
INSERT INTO permission VALUES (NULL, 'campaign_delete', '');
INSERT INTO permission VALUES (NULL, 'campaign_start', '');
INSERT INTO permission VALUES (NULL, 'campaign_stop', '');

-- permissions for user role
SELECT @roleId := role_id FROM role WHERE name='user';
SELECT @permissionId := permission_id FROM permission WHERE name='group';
INSERT INTO role_permission VALUES (NULL, @roleId, @permissionId);   /* group */
SELECT @permissionId := permission_id FROM permission WHERE name='campaign';
INSERT INTO role_permission VALUES (NULL, @roleId, @permissionId);   /* campaign */

