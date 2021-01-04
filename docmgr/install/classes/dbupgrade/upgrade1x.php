<?php

class UPGRADE1X
{

  private $DB;
  
  function __construct($DB)
  {
    $this->DB = $DB;
  }

  //update to 2012-05-20-01
  function upgrade124() 
  {
 
    $sql = "DROP VIEW docmgr.dm_view_search;
            CREATE VIEW docmgr.dm_view_search AS
            SELECT dm_object.id, dm_object.name, dm_object.summary, dm_object.object_owner, 
            dm_object.filesize, dm_object.last_modified, dm_index.idxfti
            FROM docmgr.dm_index
            LEFT JOIN docmgr.dm_object ON dm_index.object_id = dm_object.id;
            ";
    $this->DB->query($sql);

  }
                                      
  //update to 2011-02-15-01
  function ver11()
  {

		$this->DB->begin();
		
		//setup new workflow object tables
		$sql = "
		
		CREATE SCHEMA auth;
		
		CREATE TABLE auth.accountperm (
		account_id integer NOT NULL,
		bitset integer DEFAULT 0 NOT NULL,
		bitmask bit(32),
		enable boolean DEFAULT true NOT NULL,
		locked_time timestamp without time zone,
		failed_logins integer DEFAULT 0 NOT NULL,
		failed_logins_locked boolean DEFAULT false NOT NULL,
		last_success_login timestamp without time zone DEFAULT '1970-01-01 00:00:00'::timestamp without time zone NOT NULL,
		setup boolean DEFAULT false,
		last_activity timestamp without time zone
		);
		
		CREATE UNIQUE INDEX accountperm_pkey ON auth.accountperm USING btree (account_id);
		
		CREATE TABLE auth.accounts (
		id serial NOT NULL,
		login text NOT NULL,
		password text NOT NULL,
		digest_hash text,
		first_name text,
		last_name text,
		email text,
		phone text
		);
		
		CREATE TABLE auth.grouplink (
		accountid integer NOT NULL,
		groupid integer NOT NULL
		);
		
		CREATE INDEX accountperm_accountid_idx on auth.grouplink using btree(accountid);
		CREATE INDEX accountperm_groupid_idx on auth.grouplink using btree(groupid);
		
		CREATE TABLE auth.groupperm (
		group_id integer NOT NULL,
		bitset integer DEFAULT 0 NOT NULL,
		bitmask bit(32)
		);
		
		CREATE UNIQUE INDEX groupperm_pkey ON auth.groupperm USING btree (group_id);
		
		CREATE TABLE auth.groups (
		id SERIAL NOT NULL,
		name text NOT NULL
		);
		
		CREATE TABLE auth.settings (
		account_id integer NOT NULL,
		language text,
		home_directory integer,
		editor text
		);
		
		CREATE UNIQUE INDEX settings_pkey ON auth.settings USING btree (account_id);
		
		INSERT INTO auth.accountperm (SELECT account_id, bitset, bitmask, enable, locked_time, failed_logins, failed_logins_locked, last_success_login, setup, last_activity FROM auth_accountperm);
		INSERT INTO auth.accounts (SELECT id,login,password,digest_hash,first_name,last_name,email,phone FROM auth_accounts);
		INSERT INTO auth.grouplink (SELECT accountid,groupid FROM auth_grouplink);
		INSERT INTO auth.groupperm (SELECT group_id,bitset,bitmask FROM auth_groupperm);
		INSERT INTO auth.groups (SELECT id,name FROM auth_groups);
		INSERT INTO auth.settings (SELECT account_id,language,home_directory,editor FROM auth_settings);
		
		SELECT SETVAL('auth.accounts_id_seq',(SELECT max(id) FROM auth.accounts));
		SELECT SETVAL('auth.groups_id_seq',(SELECT max(id) FROM auth.groups));
		
		UPDATE auth.settings SET language='en';
		";
		
		$this->DB->query($sql);
		
		$sql = "
		          DROP TABLE auth_accountperm CASCADE;
		          DROP TABLE auth_accounts CASCADE;
		          DROP TABLE auth_grouplink CASCADE;
		          DROP TABLE auth_groupperm CASCADE;
		          DROP TABLE auth_groups CASCADE;
		          DROP TABLE auth_settings CASCADE;
		          ";
		
		$this->DB->query($sql);
		
		
		//setup new workflow object tables
		$sql = "
		
		ALTER TABLE docmgr.dm_workflow ADD COLUMN name TEXT;
		
		UPDATE docmgr.dm_workflow SET name=(SELECT name FROM docmgr.dm_object WHERE id=dm_workflow.object_id);
		
		CREATE TABLE docmgr.dm_workflow_object (
		workflow_id integer not null, 
		object_id integer not null
		);
		
		CREATE INDEX dm_workflow_object_workflow_id_idx ON docmgr.dm_workflow_object USING btree(workflow_id);
		
		CREATE TABLE docmgr.dm_workflow_route_object (
		route_id integer not null,
		object_id integer not null,
		completed boolean default false
		);
		
		CREATE INDEX dm_workflow_route_object_route_id_idx ON docmgr.dm_workflow_route_object USING btree(route_id);
		CREATE INDEX dm_workflow_route_object_object_id_idx ON docmgr.dm_workflow_route_object USING btree(object_id);
		
		INSERT INTO docmgr.dm_workflow_object (SELECT id,object_id FROM docmgr.dm_workflow);
		
		INSERT INTO docmgr.dm_workflow_route_object
		  (
		    SELECT id, 
		      (SELECT object_id FROM docmgr.dm_workflow WHERE id=docmgr.dm_workflow_route.workflow_id),
		      'f' 
		      FROM docmgr.dm_workflow_route
		  );
		
		ALTER TABLE docmgr.dm_workflow DROP COLUMN object_id CASCADE;
		
		CREATE VIEW docmgr.dm_view_workflow AS
		SELECT dm_workflow_route.id, dm_workflow_route.workflow_id, dm_workflow_route.account_id, dm_workflow_route.task_type,
		dm_workflow_route.date_due AS relative_due, dm_workflow_route.date_complete, dm_workflow_route.status,
		dm_workflow_route.sort_order, dm_workflow_route.comment FROM
		(docmgr.dm_workflow_route LEFT JOIN docmgr.dm_workflow ON ((dm_workflow_route.workflow_id = dm_workflow.id)));
		
		CREATE VIEW docmgr.dm_view_workflow_route AS SELECT dm_workflow_route.*,dm_workflow_route_object.* FROM
		docmgr.dm_workflow_route LEFT JOIN docmgr.dm_workflow_route_object ON
		dm_workflow_route.id = dm_workflow_route_object.route_id;
		
		CREATE TABLE task.workflow_task (
		task_id integer NOT NULL,
		workflow_id integer NOT NULL,
		route_id integer NOT NULL
		);
		
		CREATE INDEX workflow_task_task_id_idx ON task.workflow_task USING btree (task_id);
		CREATE INDEX workflow_task_workflow_id_idx ON task.workflow_task USING btree (workflow_id);
		CREATE INDEX workflow_task_route_id_idx ON task.workflow_task USING btree (route_id);
		
		CREATE VIEW task.view_workflow_task AS
		SELECT task.id, task.title, task.notes, task.priority, task.date_due, task.completed, task.due,
		task.date_completed, task.created_by, task.created_date, task.modified_by, task.modified_date, task.task_type, task.idxfti,
		workflow_task.task_id, workflow_task.route_id, workflow_task.workflow_id
		FROM (task.task LEFT JOIN task.workflow_task ON ((task.id = workflow_task.task_id)));
		
		INSERT INTO task.workflow_task (SELECT task_id,workflow_id,route_id FROM task.docmgr_task);
		
		ALTER TABLE logger.logs DROP COLUMN sql;
		ALTER TABLE logger.logs DROP COLUMN post_data;
		ALTER TABLE logger.logs DROP COLUMN get_data;
		ALTER TABLE logger.logs DROP COLUMN child_location_id;
		ALTER TABLE logger.logs ADD COLUMN data text;
		
		CREATE INDEX log_level_idx ON logger.logs USING btree (level);
		CREATE INDEX logs_category_idx ON logger.logs USING btree (category);
		CREATE UNIQUE INDEX logs_pkey ON logger.logs USING btree (id);
		
		";
		
		$this->DB->query($sql);
		
		$this->DB->end();
		  
  }


  //upgrade RC11-13 to RC1
  function verRC14()
  {

    $this->DB->begin();
  
    $sql = "DROP VIEW docmgr.dm_view_collections;
              CREATE VIEW docmgr.dm_view_collections AS
                SELECT dm_object.id, dm_object.name, dm_object.summary, dm_object.object_type, dm_object.create_date, 
                dm_object.object_owner, dm_object.status, dm_object.status_date, dm_object.status_owner, dm_object.version, 
                dm_object.reindex, dm_object.hidden, dm_object_parent.object_id, dm_object_parent.parent_id, dm_object_perm.account_id, 
                dm_object_perm.group_id, dm_object_perm.bitset,dm_object_perm.bitmask
                FROM docmgr.dm_object
                LEFT JOIN docmgr.dm_object_parent ON dm_object.id = dm_object_parent.object_id
                LEFT JOIN docmgr.dm_object_perm ON dm_object.id = dm_object_perm.object_id
                WHERE dm_object.object_type = 'collection'::text;";
            
    $this->DB->query($sql);
  
    //change alert names
    $sql = "UPDATE docmgr.dm_subscribe SET event_type='OBJ_LOCK_ALERT' WHERE event_type='OBJ_CHECKOUT_ALERT';";
    $sql .= "UPDATE docmgr.dm_subscribe SET event_type='OBJ_UNLOCK_ALERT' WHERE event_type='OBJ_CHECKIN_ALERT';";
    $sql .= "UPDATE docmgr.dm_alert SET alert_type='OBJ_LOCK_ALERT' WHERE alert_type='OBJ_CHECKOUT_ALERT';";
    $sql .= "UPDATE docmgr.dm_alert SET alert_type='OBJ_UNLOCK_ALERT' WHERE alert_type='OBJ_CHECKIN_ALERT';";
    $this->DB->query($sql);

    //function update
    $sql = "
            CREATE FUNCTION docmgr.path_to_id(path text) RETURNS text
            LANGUAGE plpgsql IMMUTABLE
            AS \$\$
            DECLARE arr text[];
            DECLARE parent integer;
            DECLARE i integer;
            DECLARE parentstr text;

            BEGIN

            arr := string_to_array(path,'/');
            parent := 0;
            parentstr := 0;

            FOR i IN array_lower(arr,1)+1 .. array_upper(arr,1) LOOP
 
              SELECT INTO parent object_id FROM docmgr.dm_view_objects WHERE parent_id=parent AND name=arr[i];
 
              SELECT INTO parentstr (parentstr || ',' || parent);

            END LOOP;
 
            RETURN parentstr;

            END;
            \$\$;
            ";
            
    $this->DB->query($sql);

    //share upgrades
    $sql = "CREATE TABLE docmgr.dm_share (
              object_id integer NOT NULL,
              account_id integer not null,
              share_account_id integer not null,
              bitmask text
            );
            ALTER TABLE docmgr.dm_object_parent ADD COLUMN account_id integer;
            ALTER TABLE docmgr.dm_object_parent ADD COLUMN share boolean DEFAULT FALSE;
            ALTER TABLE docmgr.dm_object_perm ADD COLUMN share boolean DEFAULT FALSE;
            ALTER TABLE docmgr.dm_object_parent ADD COLUMN workflow_id integer DEFAULT 0;
            ALTER TABLE docmgr.dm_object_perm ADD COLUMN workflow_id integer DEFAULT 0;
            UPDATE docmgr.dm_object_parent SET account_id=(SELECT object_owner FROM docmgr.dm_object WHERE id=dm_object_parent.object_id);
            ";
    $this->DB->query($sql);

    //view for folder recursion
    $sql = "CREATE VIEW docmgr.dm_view_parent AS
            SELECT docmgr.dm_object_parent.*,dm_object.name,
            dm_object.object_type FROM docmgr.dm_object_parent
            LEFT JOIN docmgr.dm_object ON dm_object_parent.object_id=dm_object.id;

            CREATE VIEW docmgr.dm_view_colsearch AS
            SELECT dm_object.id, dm_object.name, dm_object.summary, dm_object.object_type, dm_object.create_date, dm_object.object_owner, 
            dm_object.status, dm_object.status_date, dm_object.status_owner, dm_object.version, dm_object.reindex, dm_object.hidden, 
            dm_object_parent.object_id, dm_object_parent.parent_id, dm_object_perm.account_id, dm_object_perm.group_id, 
            dm_object_perm.bitset, dm_object_perm.bitmask
            FROM docmgr.dm_object
            LEFT JOIN docmgr.dm_object_parent ON dm_object.id = dm_object_parent.object_id
            LEFT JOIN docmgr.dm_object_perm ON dm_object.id = dm_object_perm.object_id
            WHERE dm_object.object_type = 'collection' OR dm_object.object_type='search';
            ";
    $this->DB->query($sql);

    $this->DB->end();
    
    //now we have to make a permissions entry for all object owners that don't have permissions
    $sql = "SELECT id,object_owner FROM docmgr.dm_object WHERE id NOT IN 
            (SELECT object_id FROM docmgr.dm_object_perm WHERE object_id=dm_object.id AND account_id=dm_object.object_owner)";
    $list = $this->DB->fetch($sql);
    
    for ($i=0;$i<$list["count"];$i++)
    {
    
      $opt = null;
      $opt["object_id"] = $list[$i]["id"];
      $opt["account_id"] = $list[$i]["object_owner"];
      $opt["bitmask"] = "00000001";
      $this->DB->insert("docmgr.dm_object_perm",$opt);
    
    }
  
  }

  //upgrades from RC9 to RC10
  function verRC10()
  {

    //recreate the db_version table and put in the current database version
    $sql = "
            DROP TABLE db_version;
            CREATE TABLE db_version (
              version integer NOT NULL
            );
            INSERT INTO db_version (version) VALUES ('".DB_VERSION."');
            ";
    $this->DB->query($sql);

    //see if they've setup dashboard for the everyone group
    $sql = "SELECT group_id FROM group_dashboard WHERE group_id='0'";
    $info = $this->DB->single($sql);

    //nope.  make our own    
    if (!$info)
    {
    
      $sql = "INSERT INTO group_dashboard VALUES ('0','1','1','home','bkmodlet','bkmodlet1');
            INSERT INTO group_dashboard VALUES ('0','1','2','home','taskmodlet','taskmodlet3');
            INSERT INTO group_dashboard VALUES ('0','2','1','home','currentsubscribe','currentsubscribe2');
            INSERT INTO group_dashboard VALUES ('0','2','2','home','subscribealert','subscribealert4');
            ";

            
      $this->DB->query($sql);
    
    }
    
  }

}
