INSERT INTO usr(username, passwd, first_name, last_name, email, active) VALUES ('admin', MD5('helloAdmin'), 'System', 'Administrator', 'admin@ictcore.org', 1);
SELECT @usrId := LAST_INSERT_ID();
SELECT @roleId := role_id FROM role WHERE name='admin';
INSERT INTO user_role VALUES (@roleId, @usrId);
SELECT @roleId := role_id FROM role WHERE name='user';
INSERT INTO user_role VALUES (@roleId, @usrId);

INSERT INTO usr(username, passwd, first_name, last_name, email, active) VALUES ('user', MD5('helloUser'), 'Test', 'User', 'user@ictcore.org', 1);
SELECT @usrId := LAST_INSERT_ID();
SELECT @roleId := role_id FROM role WHERE name='user';
INSERT INTO user_role VALUES (@roleId, @usrId);
