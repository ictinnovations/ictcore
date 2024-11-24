/*==============================================================*/
/* Table: forgot_password                                       */
/*==============================================================*/
CREATE TABLE forgot_password
(
   id                             int(11) unsigned       NOT NULL auto_increment,
   usr_id                         int(11)                default NULL,
   email                          varchar(128)           default NULL,
   token                          longtext               default NULL,
   created_at                     int(11)                default NULL,
   PRIMARY KEY  (id)
) ENGINE = InnoDB;