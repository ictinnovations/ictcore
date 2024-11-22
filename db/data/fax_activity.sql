/* Fax activity feature */
/*==============================================================*/
/* Table: faxactivity                                           */
/* Desc: this table provide store main information related to   */
/* activity like download and view on fax.                      */
/*                                                              */
/* TODO: handle / keep data of previous / multi attempts.       */
/*==============================================================*/
CREATE TABLE faxactivity (
   id                              INT(8)                 NOT NULL AUTO_INCREMENT,
   faxid                           INT(5)                 DEFAULT NULL,
   faxactivity                     VARCHAR(250)           DEFAULT NULL,
   date                            VARCHAR(250)           DEFAULT NULL,
   PRIMARY KEY (id)
) ENGINE=InnoDB;


/*==============================================================*/
/* Table: faxlogs                                               */
/* Desc: this table provide store main information related to   */
/* each fax.                                                    */
/*                                                              */
/* TODO: handle / keep data of previous / multi attempts.       */
/*==============================================================*/
CREATE TABLE faxlogs (
   id                             INT(7)                  NOT NULL AUTO_INCREMENT,
   faxid                          INT(7)                  DEFAULT NULL,
   sourceid                       INT(9)                  DEFAULT NULL,
   sourcename                     VARCHAR(250)            DEFAULT NULL,
   sourcephone                    VARCHAR(250)            DEFAULT NULL,
   callerid                       VARCHAR(50)             DEFAULT NULL,
   destination                    VARCHAR(250)            DEFAULT NULL,
   destinationname                VARCHAR(250)            DEFAULT NULL,
   faxstatus                      LONGTEXT                DEFAULT NULL,
   pages                          int(5)                  DEFAULT NULL,
   coverpage                      VARCHAR(5)              DEFAULT NULL,
   duration                       VARCHAR(250)            DEFAULT NULL,
   pending                        VARCHAR(255)            DEFAULT NULL,
   processing                     VARCHAR(255)            DEFAULT NULL,
   result                         VARCHAR(255)            DEFAULT NULL,
   response                       VARCHAR(255)            DEFAULT NULL,
   origin                         VARCHAR(200)            DEFAULT NULL,
   date                           DATETIME                DEFAULT NULL,
   PRIMARY KEY (id)
) ENGINE=InnoDB;