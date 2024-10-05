INSERT INTO role(name, description) VALUES ('admin', 'system administrator');

-- permissions for admin role
SELECT @roleId := role_id FROM role WHERE name='admin';
-- provider permissions
SELECT @permissionId := permission_id FROM permission WHERE name='api';
INSERT INTO role_permission VALUES (NULL, @roleId, @permissionId);   /* api */
SELECT @permissionId := permission_id FROM permission WHERE name='statistic';
INSERT INTO role_permission VALUES (NULL, @roleId, @permissionId);   /* statistic */
SELECT @permissionId := permission_id FROM permission WHERE name='configuration';
INSERT INTO role_permission VALUES (NULL, @roleId, @permissionId);   /* configuration */
SELECT @permissionId := permission_id FROM permission WHERE name='user';
INSERT INTO role_permission VALUES (NULL, @roleId, @permissionId);   /* user */
SELECT @permissionId := permission_id FROM permission WHERE name='usr';
INSERT INTO role_permission VALUES (NULL, @roleId, @permissionId);   /* usr */
SELECT @permissionId := permission_id FROM permission WHERE name='role';
INSERT INTO role_permission VALUES (NULL, @roleId, @permissionId);   /* role */
SELECT @permissionId := permission_id FROM permission WHERE name='permission';
INSERT INTO role_permission VALUES (NULL, @roleId, @permissionId);   /* permission */
SELECT @permissionId := permission_id FROM permission WHERE name='account';
INSERT INTO role_permission VALUES (NULL, @roleId, @permissionId);   /* account */
SELECT @permissionId := permission_id FROM permission WHERE name='provider';
INSERT INTO role_permission VALUES (NULL, @roleId, @permissionId);   /* provider */
SELECT @permissionId := permission_id FROM permission WHERE name='tenant';
INSERT INTO role_permission VALUES (NULL, @roleId, @permissionId);   /* tenant */

/* Additional Permissions */
SELECT @permissionId := permission_id FROM permission WHERE name='transmission';
INSERT INTO role_permission VALUES (NULL, @roleId, @permissionId);   /* transmission */
SELECT @permissionId := permission_id FROM permission WHERE name='document';
INSERT INTO role_permission VALUES (NULL, @roleId, @permissionId);   /* document */
SELECT @permissionId := permission_id FROM permission WHERE name='contact';
INSERT INTO role_permission VALUES (NULL, @roleId, @permissionId);   /* contact */
SELECT @permissionId := permission_id FROM permission WHERE name='group';
INSERT INTO role_permission VALUES (NULL, @roleId, @permissionId);   /* group */
SELECT @permissionId := permission_id FROM permission WHERE name='campaign';
INSERT INTO role_permission VALUES (NULL, @roleId, @permissionId);   /* campaign */
