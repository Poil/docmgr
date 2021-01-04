<?php

class UPGRADE2X
{

	private $DB;

	function __construct($DB)
	{
		$this->DB = $DB;
	}

	function ver21()
	{
	
	  $this->DB->begin();
	  
	  //add ability to set a bookmark as the default browse path
	  $sql = "ALTER TABLE docmgr.dm_bookmark ADD COLUMN default_browse BOOLEAN NOT NULL DEFAULT FALSE;";
	  $this->DB->query($sql);

	  //add ability to set a bookmark as the default browse path
	  $sql = "
          CREATE TABLE docmgr.object_convert_keys (
            object_id integer NOT NULL,
            convert_key text NOT NULL,
            date_created timestamp without time zone NOT NULL DEFAULT NOW()
            );	  
        CREATE UNIQUE INDEX object_convert_keys_pkey ON docmgr.object_convert_keys USING btree(object_id,convert_key);
        ";
        
	  $this->DB->query($sql);
		
		$this->DB->end();
		
		return true;
		
  }

	function ver20()
	{
	
		$this->DB->begin();
		
		$sql = file_get_contents("install/classes/dbupgrade/sql-upgrade-20/auth.sql");
		$this->DB->query($sql);

		$sql = "INSERT INTO auth.account_permissions
		         SELECT account_id,enable,locked_time,failed_logins,failed_logins_locked,
		                last_success_login,setup,last_activity,bitmask FROM auth_bak.accountperm";
		$this->DB->query($sql);
		
		$sql = "INSERT INTO auth.accounts (id,login,password,digest_hash,first_name,last_name,
		                                    email,work_phone) 
		         SELECT id,login,password,digest_hash,first_name,last_name,email,phone FROM auth_bak.accounts";
		$this->DB->query($sql);
		
		$sql = "INSERT INTO auth.account_groups (account_id,group_id) SELECT accountid,groupid
		                    FROM auth_bak.grouplink;";
		$this->DB->query($sql);
		
		$sql = "INSERT INTO auth.group_permissions (group_id,bitmask)
		         SELECT group_id,bitmask FROM auth_bak.groupperm";
		$this->DB->query($sql);
		
		$sql = "INSERT INTO auth.groups (id,name) 
		         SELECT id,name FROM auth_bak.groups";
		$this->DB->query($sql);
		
		$sql = "INSERT INTO auth.account_config (account_id,language,home_directory,editor)
		         SELECT account_id,language,home_directory,editor FROM auth_bak.settings";
		$this->DB->query($sql);
		
		$sql = "SELECT SETVAL('auth.accounts_id_seq',(SELECT max(id) FROM auth.accounts))";
		$this->DB->query($sql);
		
		$sql = "SELECT SETVAL('auth.groups_id_seq',(SELECT max(id) FROM auth.groups));";
		$this->DB->query($sql);

		$sql = "SET search_path = public, pg_catalog;";
		$this->DB->query($sql);
		
		$this->DB->end();

		//bail if it didn't work		
		if ($this->DB->error()) return false;

		$this->DB->begin();
		
		//do our structure upgrade
		$sql = file_get_contents("install/classes/dbupgrade/sql-upgrade-20/docmgr.sql");
		$this->DB->query($sql);

		//transfer saved searches		
		$sql = "SELECT id,name,object_owner,params FROM docmgr.dm_search LEFT JOIN docmgr.dm_object 
		                ON dm_search.object_id = dm_object.id";
		$results = $this->DB->fetch($sql);
		
		for ($i=0;$i<$results["count"];$i++)
		{
		  $opt = null;
		  $opt["name"] = sanitize($results[$i]["name"]);
		  $opt["account_id"] = $results[$i]["object_owner"];
		  $opt["params"] = sanitize($results[$i]["params"]);
		  $this->DB->insert("docmgr.saved_searches",$opt);
		}
		
		//transfer subscriptions
		$sql = "SELECT * FROM docmgr.dm_subscribe";
		$results = $this->DB->fetch($sql);
		
		for ($i=0;$i<$results["count"];$i++)
		{
		
		  $opt = null;
		  $opt["object_id"] = $results[$i]["object_id"];
		  $opt["account_id"] = $results[$i]["account_id"];
		  $opt["notify_send_file"] = $results[$i]["send_file"];
		
		  $et = $results[$i]["event_type"];
		  
		  if ($et=="OBJ_LOCK_ALERT") $opt["locked"] = "t";
		  else if ($et=="OBJ_UNLOCK_ALERT") $opt["unlocked"] = "t";
		  else if ($et=="OBJ_REMOVE_ALERT") $opt["removed"] = "t";
		  else if ($et=="OBJ_CREATE_ALERT") $opt["created"] = "t";
		  else if ($et=="OBJ_COMMENT_POST_ALERT") $opt["comment_posted"] = "t";
		  
		  $this->DB->insert("docmgr.subscriptions",$opt);
		
		}
		
		$sql = "SET search_path = public, pg_catalog;";
		$this->DB->query($sql);
		
		$this->DB->end();

		if ($this->DB->error()) return false;
		
		/**
		  tasks to notifications
		  */
		
		$this->DB->begin();
		
		$sql = file_get_contents("install/classes/dbupgrade/sql-upgrade-20/notification.sql");
		$this->DB->query($sql);
		
		$sql = "SELECT * FROM task.view_workflow_task WHERE completed='f'";
		$results = $this->DB->fetch($sql);
		
		define("WORKFLOW_EDIT_NOTIFICATION","8");
		define("WORKFLOW_APPROVE_NOTIFICATION","10");
		define("WORKFLOW_COMMENT_NOTIFICATION","9");
		define("WORKFLOW_VIEW_NOTIFICATION","7");
		
		for ($i=0;$i<$results["count"];$i++)
		{

		  //bail because we may have some incomplete workflows
		  if (!$results[$i]["workflow_id"] || !$results[$i]["route_id"]) continue;
		
		  $link = "index.php?module=workflow&workflowId=".$results[$i]["workflow_id"]."&routeId=".$results[$i]["route_id"];
		
		  $sql = "SELECT name FROM docmgr.dm_workflow WHERE id='".$results[$i]["workflow_id"]."'";
		  $info = $this->DB->single($sql);
		
		  $sql = "SELECT task_type FROM docmgr.dm_workflow_route WHERE id='".$results[$i]["route_id"]."'";
		  $data = $this->DB->single($sql);
		
		  //set our notification type
		  if ($data["task_type"]=="edit") $type = WORKFLOW_EDIT_NOTIFICATION;	
		  elseif ($data["task_type"]=="approve") $type = WORKFLOW_APPROVE_NOTIFICATION;
		  elseif ($data["task_type"]=="comment") $type = WORKFLOW_COMMENT_NOTIFICATION;
		  else $type = WORKFLOW_VIEW_NOTIFICATION;
		
		  $sql = "SELECT account_id FROM task.task_account WHERE task_id='".$results[$i]["id"]."'";
		  $acc = $this->DB->single($sql);
		                        
		  //id,record_id,record_name,option_id,account_id,date_created,link,message,attach
		  $opt = null;
		  $opt["account_id"] = $acc["account_id"];
		  $opt["record_id"] = $results[$i]["route_id"];
		  $opt["record_name"] = sanitize($info["name"]);
		  $opt["option_id"] = $type;
		  $opt["date_created"] = $results[$i]["created_date"];
		  $opt["message"] = sanitize($results[$i]["title"]);
		  $opt["link"] = $link;
		
		  $this->DB->insert("notification.notifications",$opt);
		  
		}

		$sql = "SET search_path = public, pg_catalog;";
		$this->DB->query($sql);
		
		$this->DB->end();
		
		if ($this->DB->error()) return false;
		
		/**
		  cleanup
		  */
		
		$this->DB->begin();

		$sql = "
				DROP SCHEMA auth_bak CASCADE;
				DROP SCHEMA modlet CASCADE;
				DROP SCHEMA task CASCADE;
				DROP TABLE public.dashboard CASCADE;
				DROP TABLE public.group_dashboard CASCADE;
				DROP TABLE public.state CASCADE;
				DROP TABLE public.dm_object_type CASCADE;
				DROP TABLE docmgr.dm_alert CASCADE;
				DROP TABLE docmgr.dm_email_anon CASCADE;
				DROP TABLE docmgr.dm_search CASCADE;
				DROP TABLE docmgr.dm_subscribe CASCADE;
				DROP TABLE docmgr.dm_tag CASCADE;
				DROP TABLE docmgr.dm_tag_link CASCADE;
				DROP TABLE docmgr.dm_task CASCADE;
				DROP TABLE docmgr.dm_thumb_queue CASCADE;
				SET search_path = public, pg_catalog;
		";
		$this->DB->query($sql);
		
		$this->DB->end();
		
		if ($this->DB->error()) return false;

		return true;
				
	}
	
}

