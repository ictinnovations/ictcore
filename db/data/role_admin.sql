INSERT INTO role(name, description) VALUES ('admin', 'system administrator');

-- permissions for admin role
SELECT @roleId := role_id FROM role WHERE name='admin';
-- provider permissions
SELECT @permissionId := permission_id FROM permission WHERE name='user';
INSERT INTO role_permission VALUES (NULL, @roleId, @permissionId);   /* user */
SELECT @permissionId := permission_id FROM permission WHERE name='role';
INSERT INTO role_permission VALUES (NULL, @roleId, @permissionId);   /* role */
SELECT @permissionId := permission_id FROM permission WHERE name='permission';
INSERT INTO role_permission VALUES (NULL, @roleId, @permissionId);   /* permission */
SELECT @permissionId := permission_id FROM permission WHERE name='account';
INSERT INTO role_permission VALUES (NULL, @roleId, @permissionId);   /* account */
SELECT @permissionId := permission_id FROM permission WHERE name='provider';
INSERT INTO role_permission VALUES (NULL, @roleId, @permissionId);   /* provider */

