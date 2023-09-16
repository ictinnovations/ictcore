/*******************************************************************/
/* Copyright © 2015 ICT Innovations Pakistan All Rights Reserved   */
/* Developed By: Nasir Iqbal                                       */
/* Website : http://www.ictinnovations.com/                        */
/* Mail : nasir@ictinnovations.com                                 */
/*******************************************************************/

/*==============================================================*/
/* Table: document                                              */
/* Desc: this table will hold documents for fax broadcasting    */
/*==============================================================*/
CREATE TABLE document
(
   document_id              int(11) unsigned       NOT NULL auto_increment,
   name                     varchar(128)           NOT NULL default '0',
   type                     varchar(8)             NOT NULL,
   file_name                varchar(128)           NOT NULL,
   description              varchar(255)           NOT NULL default '',
   ocr                      blob                   default NULL,
   pages                    int(11)                NOT NULL default 0,
   size_x                   int(11)                NOT NULL default 0,
   size_y                   int(11)                NOT NULL default 0,
   quality                  ENUM('basic', 'standard', 'fine', 'super', 'superior', 'ultra') default 'standard',
   resolution_x             int(11)                NOT NULL default 0,
   resolution_y             int(11)                NOT NULL default 0,
   date_created             int(11)                default NULL,
   created_by               int(11) unsigned       default NULL,
   last_updated             int(11)                default NULL,
   updated_by               int(11) unsigned       default NULL,
   PRIMARY KEY (document_id)
) ENGINE = InnoDB;
CREATE INDEX document_created_by ON document (created_by);

/*==============================================================*/
/* Desc: Dumping Default System configurations                  */
/*==============================================================*/
-- service
INSERT INTO configuration VALUES (NULL,'service','fax_status','0',254); --ready

/*==============================================================*/
/* Table: insert fax module permissions                         */
/*==============================================================*/
-- Document permissions
INSERT INTO permission VALUES (NULL, 'document', '');
INSERT INTO permission VALUES (NULL, 'document_create', '');
INSERT INTO permission VALUES (NULL, 'document_list', '');
INSERT INTO permission VALUES (NULL, 'document_read', '');
INSERT INTO permission VALUES (NULL, 'document_update', '');
INSERT INTO permission VALUES (NULL, 'document_delete', '');
