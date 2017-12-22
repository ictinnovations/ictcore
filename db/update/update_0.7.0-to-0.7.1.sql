-- WARNING:
-- WARNING: following update will clear all existing scheduled task
-- WARNING:

-- if not exists (select your column from syscolumns)
-- begin
--    alter yourTable add yourColumn <type> NULL
--    update yourTable set yourColumn = oldColumn
--    alter yourTable drop oldColumn
-- end
-- if not exists (select your table from sysobjects)
-- end

-- OR

-- SET @expectedVersion = '10.0.217'
-- SET @newVersion = '10.0.218'
-- SELECT @currentVersion := (SELECT version FROM table_version WHERE name='mytable' DESC LIMIT 1)
-- IF @currentVersion = @expectedVersion
-- begin
-- end

-- Task permissions
INSERT INTO permission VALUES (NULL, 'task', '');
INSERT INTO permission VALUES (NULL, 'task_create', '');
INSERT INTO permission VALUES (NULL, 'task_read', '');
INSERT INTO permission VALUES (NULL, 'task_list', '');
INSERT INTO permission VALUES (NULL, 'task_delete', '');

/*==============================================================*/
/* Table: tasks                                                 */
/* Desc: list of todo tasks / schedules                         */
/*==============================================================*/
CREATE TABLE task
(
   task_id                       int(11) unsigned       NOT NULL auto_increment,
   type                          varchar(64)            NOT NULL default '',
   action                        varchar(64)            NOT NULL default 0,
   data                          text,
   weight                        int(4)                 NOT NULL default 0,
   status                        int(4)                 NOT NULL default 0,
   is_recurring                  int(4)                 NOT NULL default 0,
   due_at                        int(11)                default NULL,
   expiry                        int(11)                default NULL,
   last_run                      int(11)                default NULL,
   account_id                    int(11)                default NULL,
   PRIMARY KEY (task_id)
) ENGINE = InnoDB;

DROP TABLE schedule;
CREATE TABLE schedule
(
   year                          varchar(50)            NOT NULL default '*',
   weekday                       varchar(50)            NOT NULL default '*',
   month                         varchar(50)            NOT NULL default '*',
   day                           varchar(50)            NOT NULL default '*',
   hour                          varchar(50)            NOT NULL default '*',
   minute                        varchar(50)            NOT NULL default '*',
   task_id                       int(11) unsigned       NOT NULL
) ENGINE = InnoDB;
CREATE INDEX schedule_task_id ON schedule (task_id);
CREATE INDEX schedule_time ON schedule (hour, minute);
ALTER TABLE schedule 
  ADD FOREIGN KEY schedule_task_delete (task_id) 
  REFERENCES task (task_id) 
    ON DELETE CASCADE 
    ON UPDATE CASCADE;

DELIMITER |
CREATE EVENT event_check_schedule
  ON SCHEDULE EVERY 1 SECOND
  DO BEGIN
    UPDATE task t JOIN schedule s ON t.task_id = s.task_id
    SET t.status = 1, 
        t.due_at = UNIX_TIMESTAMP(),
        t.expiry = (UNIX_TIMESTAMP() + 300)
    WHERE t.status IN (0, 2, 3)
      AND (t.last_run IS NULL OR (t.last_run + 59) < UNIX_TIMESTAMP())
      AND (s.year = '*'     OR s.year = YEAR(CURDATE())) 
      AND (s.month = '*'    OR s.month = MONTH(CURDATE())) 
      AND (s.day = '*'      OR s.day = DAYOFMONTH(CURDATE())) 
      AND (s.weekday = '*'  OR s.weekday = DAYOFWEEK(CURDATE())) 
      AND (s.hour = '*'     OR s.hour = HOUR(CURTIME())) 
      AND (s.minute = '*'   OR s.minute = MINUTE(CURTIME()));
  END;
|
DELIMITER ;

ALTER TABLE account ADD type                           varchar(32)            default NULL;

ALTER TABLE provider DROP gateway_flag;
ALTER TABLE provider DROP technology_id;
ALTER TABLE provider MODIFY type type                          varchar(32)            default NULL;

DROP TABLE technology;

DROP TRIGGER usr_insert;
DELIMITER |
CREATE TRIGGER usr_insert AFTER INSERT
  ON usr FOR EACH ROW BEGIN
    INSERT INTO account (account_id, type, username, passwd, passwd_pin, first_name, last_name, phone, email, address,
                         active, date_created, created_by, last_updated, updated_by)
    SELECT NULL, 'extension', NEW.username, NEW.passwd, LEFT(RAND()*999999, 4), NEW.first_name, NEW.last_name, NEW.phone,
           NEW.email, NEW.address, NEW.active, NEW.date_created, NEW.usr_id, NULL, NULL;
  END;
|
DELIMITER ;

