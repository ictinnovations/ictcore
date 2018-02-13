INSERT INTO role(name, description) VALUES ('user', 'authorized user');

-- permissions for user role
SELECT @roleId := role_id FROM role WHERE name='user';
-- provider permissions
SELECT @permissionId := permission_id FROM permission WHERE name='api';
INSERT INTO role_permission VALUES (NULL, @roleId, @permissionId);   /* api */
SELECT @permissionId := permission_id FROM permission WHERE name='statistic_read';
INSERT INTO role_permission VALUES (NULL, @roleId, @permissionId);   /* statistic_read */
SELECT @permissionId := permission_id FROM permission WHERE name='configuration_read';
INSERT INTO role_permission VALUES (NULL, @roleId, @permissionId);   /* configuration_read */
SELECT @permissionId := permission_id FROM permission WHERE name='contact';
INSERT INTO role_permission VALUES (NULL, @roleId, @permissionId);   /* contact */
SELECT @permissionId := permission_id FROM permission WHERE name='document';
INSERT INTO role_permission VALUES (NULL, @roleId, @permissionId);   /* document */
SELECT @permissionId := permission_id FROM permission WHERE name='recording';
INSERT INTO role_permission VALUES (NULL, @roleId, @permissionId);   /* recording */
SELECT @permissionId := permission_id FROM permission WHERE name='template';
INSERT INTO role_permission VALUES (NULL, @roleId, @permissionId);   /* template */
SELECT @permissionId := permission_id FROM permission WHERE name='text';
INSERT INTO role_permission VALUES (NULL, @roleId, @permissionId);   /* text */
SELECT @permissionId := permission_id FROM permission WHERE name='program';
INSERT INTO role_permission VALUES (NULL, @roleId, @permissionId);   /* program */
SELECT @permissionId := permission_id FROM permission WHERE name='transmission';
INSERT INTO role_permission VALUES (NULL, @roleId, @permissionId);   /* transmission */
SELECT @permissionId := permission_id FROM permission WHERE name='task';
INSERT INTO role_permission VALUES (NULL, @roleId, @permissionId);   /* task */
SELECT @permissionId := permission_id FROM permission WHERE name='schedule';
INSERT INTO role_permission VALUES (NULL, @roleId, @permissionId);   /* schedule */
SELECT @permissionId := permission_id FROM permission WHERE name='spool';
INSERT INTO role_permission VALUES (NULL, @roleId, @permissionId);   /* spool */
SELECT @permissionId := permission_id FROM permission WHERE name='result';
INSERT INTO role_permission VALUES (NULL, @roleId, @permissionId);   /* result */
SELECT @permissionId := permission_id FROM permission WHERE name='group';
INSERT INTO role_permission VALUES (NULL, @roleId, @permissionId);   /* group */
SELECT @permissionId := permission_id FROM permission WHERE name='campaign';
INSERT INTO role_permission VALUES (NULL, @roleId, @permissionId);   /* campaign */
