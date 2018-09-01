/*******************************************************************/
/* Copyright © 2015 ICT Innovations Pakistan All Rights Reserved   */
/* Developed By: Nasir Iqbal                                       */
/* Website : http://www.ictinnovations.com/                        */
/* Mail : nasir@ictinnovations.com                                 */
/*******************************************************************/

/*==============================================================*/
/* Table: template                                              */
/* Desc: this table will hold templates for email broadcasting  */
/*==============================================================*/
CREATE TABLE template
(
   template_id              int(11) unsigned       NOT NULL auto_increment,
   name                     varchar(128)           NOT NULL,
   type                     varchar(8)             NOT NULL default 1,
   description              varchar(255)           NOT NULL default '',
   subject                  varchar(255)           NOT NULL default '',
   body                     text,
   body_alt                 text,
   attachment               text,
   length                   int(11)                NOT NULL default 0,
   date_created             int(11)                default NULL,
   created_by               int(11) unsigned       default NULL,
   last_updated             int(11)                default NULL,
   updated_by               int(11) unsigned       default NULL,
   PRIMARY KEY (template_id)
) ENGINE = InnoDB;
CREATE INDEX template_created_by ON template (created_by);

/*==============================================================*/
/* Desc: Dumping Default System configurations                  */
/*==============================================================*/
-- service
INSERT INTO configuration VALUES (NULL,'service','email_status','0',254); --ready

/*==============================================================*/
/* Table: insert email module permissions                       */
/*==============================================================*/
-- Template permissions
INSERT INTO permission VALUES (NULL, 'template', '');
INSERT INTO permission VALUES (NULL, 'template_create', '');
INSERT INTO permission VALUES (NULL, 'template_list', '');
INSERT INTO permission VALUES (NULL, 'template_read', '');
INSERT INTO permission VALUES (NULL, 'template_update', '');
INSERT INTO permission VALUES (NULL, 'template_delete', '');
