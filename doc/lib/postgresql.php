<?php
/*****************************************************************************************
  Fileame: postgresql.php

  Purpose: Contains the functions required for the wrapper to access a PostgreSQL Database

  Created: 11-01-00
  Updated: 05-02-2005 - added db_close function
  Updated: 09-01-2005 - added db_escape_string function

******************************************************************************************/

//set our defines

if (!defined("DB_LIKE")) define("DB_LIKE","ILIKE");

//Opens a connection to the desired database w/ the given connection information
function db_connect($dbhost,$dbuser,$dbpassword,$dbport,$dbname) {

	if (!$conn = pg_connect("host=$dbhost port=$dbport user=$dbuser password=$dbpassword dbname=$dbname")) echo "Could not connect to database";
	return $conn;

}

//close the connection to the database
function db_close($conn) {
	
	pg_close($conn);
	
}

//this function returns an associate array with the arr[row][field] index layout
function list_result($conn,$sql) {

	if (!$result = pg_query($conn,$sql)) {
		if (defined("DEV_MODE")) debug(1,"ERROR_SQL=".$sql);
		return false;
	}
	
	$num = pg_numrows($result);

	if ($num!=0) $arr = pg_fetch_all($result);

	$arr["count"] = $num;
	return $arr;

}

//this function will return one array which contains all the results from the
//query in their respective associative array.  
function total_result($conn,$sql) {

	if (!$result = pg_query($conn,$sql)) {
		if (defined("DEV_MODE")) debug(1,"ERROR_SQL=".$sql);
		return false;
	}
	
	$num = pg_numrows($result);

	$returnArray = array();

	if ($num!=0) {

		$arr  = pg_fetch_all($result);

		foreach ($arr AS $keymaster => $value) {

			foreach($value AS $key => $element) $returnArray[$key][$keymaster] = $element;

		}
		$returnArray["count"] = $num;
		return $returnArray;

	} else return false;


	//array format will = array[key][row];

}

//this function will return the associative array that is resulted
//from a single hit query statement
function single_result($conn,$sql) {

	if (!$result = pg_query($conn,$sql)) {
		if (defined("DEV_MODE")) debug(1,"ERROR_SQL=".$sql);
		return false;
	}
	
	$num = pg_numrows($result);

	if ($num != 0) {

		$value = @pg_fetch_array($result,0);
		return $value;

	}
	else return false;

}

//this function will return only the number of results for the query
function num_result($conn,$sql) {

	if (!$result = pg_query($conn,$sql)) {
		if (defined("DEV_MODE")) debug(1,"ERROR_SQL=".$sql);
		return false;
	}
	
	return pg_numrows($result);

}

//For generic database queries.  Can be used to ADD, INSERT, UPDATE, or DELETE records
function db_query($conn,$sql) {

	if (!$result = pg_query($conn,$sql)) {
		if (defined("DEV_MODE")) debug(1,"ERROR_SQL=".$sql);
	}
	return $result;
}

//returns the id of the last created row
function db_insert_id($table,$id,$conn,$result) {

	//use oids to return the last inserted id for older versions of postgresql (pre 8.1)
	if (defined("USE_OID")) {

		//it didn't work, try again with the old method
		if (!$return_id) {
			$pgoid=@pg_last_oid($result);
			$result1=@pg_exec($conn,"SELECT $id FROM $table WHERE oid='$pgoid'");
			$query_myrow=@pg_fetch_array($result1,0);
			$return_id=$query_myrow[$id];
		}
	

	} else {
	
		$sql = "SELECT LASTVAL()";
		if ($res = @pg_exec($conn,$sql)) {
			$query_myrow=@pg_fetch_array($res,0);
			$return_id=$query_myrow[0];
		} 

	}

	return $return_id;

}

//begins a transaction instance
function beginTransaction($conn) {
	$sql = "BEGIN WORK";
	if (db_query($conn,$sql)) return true;
	else return false;
}

//ends a transaction instance
function endTransaction($conn) {
	$sql = "END WORK";
	if (db_query($conn,$sql)) return true;
	else return false;
}

//vacuum the database
function db_vacuum($conn) {

	$sql = "VACUUM FULL ANALYZE";

	if (db_query($conn,$sql)) $message = "Database Vacuumed Successfully";
	else $message = "Database Vacuum Failed";

	return $message;

}

//escape a string to make it safe for db entry
function db_escape_string($str) {

	return pg_escape_string($str);
	
}

function db_unescape_string($str) {

	return str_replace("''","'",$str);

}

//increments a sequence and returns the new value
function db_increment_seq($conn,$seq) {

	$sql = "SELECT NEXTVAL('".$seq."');";
	$info = single_result($conn,$sql);
	return $info[0];

}

//returns the current value of a sequence
function db_return_seq($conn,$seq) {

	$sql = "SELECT CURVAL('".$seq."');";
	$info = single_result($conn,$sql);
	return $info[0];

}
