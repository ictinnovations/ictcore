/*******************************************************************/
/* Copyright © 2015 ICT Innovations Pakistan All Rights Reserved   */
/* Developed By: Nasir Iqbal                                       */
/* Website : http://www.ictinnovations.com/                        */
/* Mail : nasir@ictinnovations.com                                 */
/*******************************************************************/

/*==============================================================*/
/* Table: recording                                             */
/* Desc: user can upload his voice recordings here              */
/*==============================================================*/
CREATE TABLE recording
(
   recording_id             int(11) unsigned       NOT NULL auto_increment,
   name                     varchar(128)           NOT NULL,
   type                     varchar(8)             NOT NULL,
   file_name                varchar(128)           NOT NULL,
   description              varchar(255)           NOT NULL default '',
   length                   int(11)                NOT NULL default 0,
   codec                    varchar(16)            NOT NULL default 'pcm',
   channel                  int(2)                 NOT NULL default 1,
   sample                   int(11)                NOT NULL default 8000,
   bitrate                  int(11)                NOT NULL default 16,
   date_created             int(11)                default NULL,
   created_by               int(11) unsigned       default NULL,
   last_updated             int(11)                default NULL,
   updated_by               int(11) unsigned       default NULL,
   PRIMARY KEY (recording_id)
) ENGINE = InnoDB;
CREATE INDEX recording_created_by ON recording (created_by);

/*==============================================================*/
/* Desc: Dumping Default System configurations                  */
/*==============================================================*/
-- service
INSERT INTO configuration VALUES (NULL,'service','voice_status','0',254); --ready

/*==============================================================*/
/* Table: insert voice module permissions                       */
/*==============================================================*/
-- Recording permissions
INSERT INTO permission VALUES (NULL, 'recording', '');
INSERT INTO permission VALUES (NULL, 'recording_create', '');
INSERT INTO permission VALUES (NULL, 'recording_list', '');
INSERT INTO permission VALUES (NULL, 'recording_read', '');
INSERT INTO permission VALUES (NULL, 'recording_update', '');
INSERT INTO permission VALUES (NULL, 'recording_delete', '');
