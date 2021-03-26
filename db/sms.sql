/*******************************************************************/
/* Copyright © 2015 ICT Innovations Pakistan All Rights Reserved   */
/* Developed By: Nasir Iqbal                                       */
/* Website : http://www.ictinnovations.com/                        */
/* Mail : nasir@ictinnovations.com                                 */
/*******************************************************************/

/*==============================================================*/
/* Table: text                                                  */
/*==============================================================*/
CREATE TABLE text
(
   text_id                  int(11) unsigned       NOT NULL auto_increment,
   name                     varchar(128)           NOT NULL,
   data                     text,
   type                     varchar(8)             NOT NULL default 'UTF-8',
   description              varchar(255)           default NULL,
   length                   int(11) unsigned       default NULL,
   class                    varchar(8)             NOT NULL default 1,
   encoding                 varchar(8)             NOT NULL default 0,
   date_created             int(11)                default NULL,
   created_by               int(11) unsigned       default NULL,
   last_updated             int(11)                default NULL,
   updated_by               int(11) unsigned       default NULL,
   PRIMARY KEY (text_id)
) ENGINE = InnoDB;

/*==============================================================*/
/* Desc: Dumping Default System configurations                  */
/*==============================================================*/
INSERT INTO configuration VALUES (NULL,'service','sms_status','0',254);

/*==============================================================*/
/* Table: insert sms module permissions                         */
/*==============================================================*/
INSERT INTO permission VALUES (NULL, 'text', '');
INSERT INTO permission VALUES (NULL, 'text_create', '');
INSERT INTO permission VALUES (NULL, 'text_list', '');
INSERT INTO permission VALUES (NULL, 'text_read', '');
INSERT INTO permission VALUES (NULL, 'text_update', '');
INSERT INTO permission VALUES (NULL, 'text_delete', '');
