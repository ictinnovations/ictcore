DROP TRIGGER IF EXISTS usr_insert;
CREATE TRIGGER usr_insert AFTER INSERT
  ON usr FOR EACH ROW BEGIN
    INSERT INTO account (account_id, type, username, passwd, passwd_pin, first_name, last_name, phone, email, address,
                         active, date_created, created_by, last_updated, updated_by)
    SELECT NULL, 'eaddress', NEW.username, NEW.passwd, LEFT(RAND()*999999, 4), NEW.first_name, NEW.last_name, NEW.phone,
           NEW.email, NEW.address, NEW.active, NEW.date_created, NEW.usr_id, NULL, NULL;
|  
CREATE TRIGGER usr_update AFTER UPDATE
  ON usr FOR EACH ROW BEGIN
    UPDATE account SET email = NEW.email WHERE type = 'eaddress' AND created_by = NEW.usr_id limit 1;

