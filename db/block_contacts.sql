/*==================================================================*/
/* Table: contact_dnc                                               */
/* Desc: user can upload contact_dnc lists here                     */
/*==================================================================*/
CREATE TABLE contact_dnc
(
   contact_dnc_id                int(11) unsigned       NOT NULL auto_increment,
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
   PRIMARY KEY (contact_dnc_id)
) ENGINE = InnoDB;