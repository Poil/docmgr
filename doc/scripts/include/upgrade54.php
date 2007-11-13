<?php

if (defined("TSEARCH2_INDEX")) $idxfield = "idxfti";
else $idxfield = "idxtext";

$sql = "

DROP VIEW dm_view_collections;
DROP VIEW dm_view_objects;
DROP VIEW dm_view_bookmarks;
DROP VIEW dm_view_alert;
DROP VIEW dm_view_keyword;
DROP VIEW dm_view_workflow;
DROP VIEW dm_task_view;
DROP VIEW dm_view_webdav;
DROP VIEW dm_view_search;
DROP VIEW dm_view_perm;

CREATE TABLE dm_index_queue (
  id SERIAL NOT NULL,
  object_id integer,
  account_id integer,
  notify_user boolean,
  create_date timestamp without time zone
);
     
CREATE TABLE auth_settings (
  account_id integer NOT NULL,
  language text
);

ALTER TABLE dm_file_history ADD COLUMN name TEXT;
ALTER TABLE dm_workflow_route ADD COLUMN task_notes TEXT;

ALTER TABLE dm_object ADD COLUMN new_object_type TEXT;
UPDATE dm_object SET new_object_type='collection' WHERE object_type='1';
UPDATE dm_object SET new_object_type='file' WHERE object_type='2';
UPDATE dm_object SET new_object_type='url' WHERE object_type='3';
UPDATE dm_object SET new_object_type='savesearch' WHERE object_type='4';

ALTER TABLE dm_object DROP COLUMN object_type CASCADE;
ALTER TABLE dm_object RENAME COLUMN new_object_type TO object_type;
CREATE INDEX dm_object_object_type_idx ON dm_object USING btree (object_type);


CREATE VIEW dm_view_collections AS
    SELECT dm_object.id, dm_object.name, dm_object.summary, dm_object.object_type, dm_object.create_date, dm_object.object_owner, dm_object.status, dm_object.status_date, dm_object.status_owner, dm_object.\"version\", dm_object.\"reindex\", dm_object_parent.object_id, dm_object_parent.parent_id, dm_object_perm.account_id, dm_object_perm.group_id, dm_object_perm.bitset FROM ((dm_object LEFT JOIN dm_object_parent ON ((dm_object.id = dm_object_parent.object_id))) LEFT JOIN dm_object_perm ON ((dm_object.id = dm_object_perm.object_id))) WHERE (dm_object.object_type = 'collection');

CREATE VIEW dm_view_objects AS
    SELECT dm_object.id, dm_object.name, dm_object.summary, dm_object.object_type, dm_object.create_date, dm_object.object_owner, dm_object.status, dm_object.status_date, dm_object.status_owner, dm_object.\"version\", dm_object.\"reindex\", dm_object.filesize, dm_object_parent.object_id, dm_object_parent.parent_id, dm_object_perm.account_id, dm_object_perm.group_id, dm_object_perm.bitset FROM ((dm_object LEFT JOIN dm_object_parent ON ((dm_object.id = dm_object_parent.object_id))) LEFT JOIN dm_object_perm ON ((dm_object.id = dm_object_perm.object_id)));

CREATE VIEW dm_view_bookmarks AS
    SELECT dm_object.id, dm_object.name, dm_object.summary, dm_object.object_type, dm_object.create_date, dm_object.object_owner, dm_object.status, dm_object.status_date, dm_object.status_owner, dm_object.\"version\", dm_object.\"reindex\", dm_bookmark.object_id, dm_bookmark.account_id FROM dm_object, dm_bookmark WHERE (dm_object.id = dm_bookmark.object_id);

CREATE VIEW dm_view_alert AS
    SELECT dm_object.id, dm_object.name, dm_object.summary, dm_object.object_type, dm_object.create_date, dm_object.object_owner, dm_object.status, dm_object.status_date, dm_object.status_owner, dm_object.\"version\", dm_object.\"reindex\", dm_alert.id AS alert_id, dm_alert.object_id, dm_alert.account_id, dm_alert.alert_type FROM dm_object, dm_alert WHERE (dm_object.id = dm_alert.object_id);

CREATE VIEW dm_view_keyword AS
    SELECT id, name, summary, object_type, create_date, object_owner, status, status_date, status_owner, filesize, dm_keyword.object_id, dm_keyword.field1, dm_keyword.field2, dm_keyword.field3, dm_keyword.field4, dm_keyword.field5, dm_keyword.field6, dm_object_parent.parent_id, dm_object_perm.account_id, dm_object_perm.group_id, dm_object_perm.bitset FROM (((dm_object LEFT JOIN dm_keyword ON ((dm_object.id = dm_keyword.object_id))) LEFT JOIN dm_object_parent ON ((dm_object.id = dm_object_parent.object_id))) LEFT JOIN dm_object_perm ON ((dm_object.id = dm_object_perm.object_id)));

CREATE VIEW dm_view_workflow AS
    SELECT dm_workflow_route.id, dm_workflow_route.workflow_id, dm_workflow_route.account_id, dm_workflow_route.task_type, dm_workflow_route.date_due AS relative_due, dm_workflow_route.date_complete, dm_workflow_route.status, dm_workflow_route.sort_order, dm_workflow_route.\"comment\", dm_workflow.object_id FROM (dm_workflow_route LEFT JOIN dm_workflow ON ((dm_workflow_route.workflow_id = dm_workflow.id)));

CREATE VIEW dm_task_view AS
    SELECT dm_task.account_id, dm_task.task_id, dm_task.alert_type, dm_workflow.object_id, dm_workflow_route.id AS route_id, dm_workflow_route.date_due, dm_workflow_route.task_notes, dm_object.name FROM (((dm_task LEFT JOIN dm_workflow_route ON ((dm_task.task_id = dm_workflow_route.id))) LEFT JOIN dm_workflow ON ((dm_workflow_route.workflow_id = dm_workflow.id))) LEFT JOIN dm_object ON ((dm_workflow.object_id = dm_object.id)));

CREATE VIEW dm_view_webdav AS
    SELECT dm_object.id, dm_object.name, dm_object.summary, dm_object.object_type, dm_object.create_date, dm_object.object_owner, dm_object.status, dm_object.status_date, dm_object.status_owner, dm_object.\"version\", dm_object_parent.object_id, dm_object_parent.parent_id, (SELECT dm_file_history.id FROM dm_file_history WHERE (dm_file_history.object_id = dm_object.id) ORDER BY dm_file_history.\"version\" DESC LIMIT 1) AS file_id FROM dm_object, dm_object_parent WHERE ((dm_object.id = dm_object_parent.object_id) AND ((dm_object.object_type = 'collection') OR (dm_object.object_type = 'file')));

CREATE VIEW dm_view_search AS
 	SELECT id, name, summary, object_type, create_date, object_owner, status, status_date, status_owner, filesize, dm_index.".$idxfield."
	FROM dm_object
   	LEFT JOIN dm_index ON dm_object.id = dm_index.object_id;

CREATE VIEW dm_view_perm AS
 	SELECT dm_object.id, dm_object.name, dm_object.summary, dm_object.object_type, dm_object.create_date, 
	dm_object.object_owner, dm_object.status, dm_object.status_date, dm_object.status_owner, dm_object.version, 
	dm_object.\"reindex\", dm_object_perm.object_id, dm_object_perm.account_id, dm_object_perm.group_id, dm_object_perm.bitset
   	FROM dm_object
   	LEFT JOIN dm_object_perm ON dm_object.id = dm_object_perm.object_id;

ALTER TABLE auth_accountperm add locked_time timestamp without time zone;
ALTER TABLE auth_accountperm add failed_logins integer;
ALTER TABLE auth_accountperm ALTER COLUMN failed_logins SET DEFAULT 0;
UPDATE auth_accountperm SET failed_logins='0';
ALTER TABLE auth_accountperm ALTER COLUMN failed_logins SET NOT NULL;
ALTER TABLE auth_accountperm add failed_logins_locked boolean;
ALTER TABLE auth_accountperm ALTER COLUMN failed_logins_locked SET DEFAULT false;
UPDATE auth_accountperm SET failed_logins_locked=FALSE;
ALTER TABLE auth_accountperm ALTER COLUMN failed_logins_locked SET NOT NULL;
ALTER TABLE auth_accountperm add last_success_login timestamp without time zone;
ALTER TABLE auth_accountperm ALTER COLUMN last_success_login SET DEFAULT '1970-01-01 00:00:00'::timestamp without time zone;
UPDATE auth_accountperm SET last_success_login='1970-01-01 00:00:00';
ALTER TABLE auth_accountperm ALTER COLUMN last_success_login SET NOT NULL;

";



if (db_query($conn,$sql)) echo "Upgrade to 0.54 completed successfully\n";
else echo "Upgrade to 0.54 failed\n";
