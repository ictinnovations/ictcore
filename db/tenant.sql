CREATE TABLE tenant
(
    tenant_id         int(11) unsigned       NOT NULL auto_increment, 
    domain            varchar(128)           NOT NULL,
    title             varchar(128)           NOT NULL,
    logo_name         varchar(128)           NOT NULL,
    footer            varchar(128)           NOT NULL,
    date_created      int(11)                default NULL,
    created_by        int(11) unsigned       default NULL,
    last_updated      int(11)                default NULL,
    updated_by        int(11) unsigned       default NULL,
    PRIMARY KEY (tenant_id)
) ENGINE = InnoDB;

-- Tenant permissions
INSERT INTO permission VALUES (NULL, 'tenant', '');
INSERT INTO permission VALUES (NULL, 'tenant_create', '');
INSERT INTO permission VALUES (NULL, 'tenant_list', '');
INSERT INTO permission VALUES (NULL, 'tenant_read', '');
INSERT INTO permission VALUES (NULL, 'tenant_update', '');
INSERT INTO permission VALUES (NULL, 'tenant_delete', '');
